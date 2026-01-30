<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * OrganizationalUnit Model
 * 
 * Manages hierarchical organizational structure for Kibaha Municipal Council
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $level
 * @property int|null $parent_id
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $population
 * @property string|null $description
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class OrganizationalUnit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'level',
        'parent_id',
        'latitude',
        'longitude',
        'population',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'population' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Organizational level constants
     */
    const LEVEL_REGION = 'REGION';
    const LEVEL_DISTRICT = 'DISTRICT';
    const LEVEL_WARD = 'WARD';
    const LEVEL_VILLAGE = 'VILLAGE';
    const LEVEL_FACILITY = 'FACILITY';

    /**
     * Get all possible organizational levels
     */
    public static function levels(): array
    {
        return [
            self::LEVEL_REGION,
            self::LEVEL_DISTRICT,
            self::LEVEL_WARD,
            self::LEVEL_VILLAGE,
            self::LEVEL_FACILITY,
        ];
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the parent organizational unit
     */
    public function parent()
    {
        return $this->belongsTo(OrganizationalUnit::class, 'parent_id');
    }

    /**
     * Get the child organizational units
     */
    public function children()
    {
        return $this->hasMany(OrganizationalUnit::class, 'parent_id');
    }

    /**
     * Get users belonging to this organizational unit
     */
    public function users()
    {
        return $this->hasMany(User::class, 'org_unit_id');
    }

    /**
     * Get projects in this organizational unit
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'org_unit_id');
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to only include active units
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by level
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $level
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to get root units (no parent)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to search units
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to include units with GPS coordinates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithGPS($query)
    {
        return $query->whereNotNull('latitude')
                    ->whereNotNull('longitude');
    }

    // ========================================================================
    // CALCULATED ATTRIBUTES
    // ========================================================================

    /**
     * Get the full hierarchical name
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        $names = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $names);
    }

    /**
     * Get the level label
     *
     * @return string
     */
    public function getLevelLabelAttribute(): string
    {
        return [
            self::LEVEL_REGION => 'Region',
            self::LEVEL_DISTRICT => 'District',
            self::LEVEL_WARD => 'Ward',
            self::LEVEL_VILLAGE => 'Village',
            self::LEVEL_FACILITY => 'Facility',
        ][$this->level] ?? $this->level;
    }

    /**
     * Get the total population including all children
     *
     * @return int
     */
    public function getTotalPopulationAttribute(): int
    {
        $total = $this->population ?? 0;

        foreach ($this->children as $child) {
            $total += $child->total_population;
        }

        return $total;
    }

    /**
     * Get the depth in hierarchy
     *
     * @return int
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    /**
     * Check if unit has GPS coordinates
     *
     * @return bool
     */
    public function hasGPSCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Get all descendants (recursive)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllDescendants()
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }

        return $descendants;
    }

    /**
     * Get all ancestors (recursive)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAncestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Get siblings (other children of the same parent)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiblings()
    {
        if (!$this->parent_id) {
            return collect();
        }

        return $this->parent->children()->where('id', '!=', $this->id)->get();
    }

    /**
     * Get the path from root to this unit
     *
     * @return array
     */
    public function getPath(): array
    {
        $path = [];
        $ancestors = $this->getAllAncestors()->reverse();

        foreach ($ancestors as $ancestor) {
            $path[] = [
                'id' => $ancestor->id,
                'name' => $ancestor->name,
                'level' => $ancestor->level,
            ];
        }

        $path[] = [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level,
        ];

        return $path;
    }

    /**
     * Get statistics for this unit
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'users_count' => $this->users()->count(),
            'projects_count' => $this->projects()->count(),
            'children_count' => $this->children()->count(),
            'total_population' => $this->total_population,
            'depth' => $this->depth,
            'has_gps' => $this->hasGPSCoordinates(),
        ];
    }

    /**
     * Get unit summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'level' => $this->level,
            'level_label' => $this->level_label,
            'population' => $this->population,
            'total_population' => $this->total_population,
            'has_gps' => $this->hasGPSCoordinates(),
            'parent_name' => $this->parent?->name,
            'children_count' => $this->children()->count(),
            'users_count' => $this->users()->count(),
            'projects_count' => $this->projects()->count(),
        ];
    }
}
