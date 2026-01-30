<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Theme Model
 * 
 * Manages project themes/categories for the KMC M&E System
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $color
 * @property string|null $icon
 * @property int $sort_order
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Theme extends Model
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
        'description',
        'color',
        'icon',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the indicators for this theme
     */
    public function indicators()
    {
        return $this->hasMany(Indicator::class);
    }

    /**
     * Get the projects for this theme
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to only include active themes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope a query to search themes
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

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Get the count of active indicators
     *
     * @return int
     */
    public function getActiveIndicatorsCount(): int
    {
        return $this->indicators()->active()->count();
    }

    /**
     * Get the count of active projects
     *
     * @return int
     */
    public function getActiveProjectsCount(): int
    {
        return $this->projects()->active()->count();
    }

    /**
     * Get the total budget of all projects in this theme
     *
     * @return float
     */
    public function getTotalBudget(): float
    {
        return $this->projects()->sum('budget') ?? 0;
    }

    /**
     * Get the total expenditures of all projects in this theme
     *
     * @return float
     */
    public function getTotalExpenditures(): float
    {
        $total = 0;
        foreach ($this->projects as $project) {
            $total += $project->total_expenditures;
        }
        return $total;
    }

    /**
     * Get the average completion percentage of all projects in this theme
     *
     * @return float
     */
    public function getAverageCompletion(): float
    {
        return $this->projects()->avg('completion_percentage') ?? 0;
    }

    /**
     * Check if theme can be deleted (no associated projects or indicators)
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return $this->projects()->count() === 0 && $this->indicators()->count() === 0;
    }

    /**
     * Get theme statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'indicators_count' => $this->indicators()->count(),
            'active_indicators_count' => $this->getActiveIndicatorsCount(),
            'projects_count' => $this->projects()->count(),
            'active_projects_count' => $this->getActiveProjectsCount(),
            'total_budget' => $this->getTotalBudget(),
            'total_expenditures' => $this->getTotalExpenditures(),
            'budget_utilization' => $this->getTotalBudget() > 0 
                ? ($this->getTotalExpenditures() / $this->getTotalBudget()) * 100 
                : 0,
            'average_completion' => $this->getAverageCompletion(),
        ];
    }

    /**
     * Get theme summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        $stats = $this->getStatistics();

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'indicators_count' => $stats['indicators_count'],
            'projects_count' => $stats['projects_count'],
            'total_budget' => number_format($stats['total_budget'], 2),
            'total_expenditures' => number_format($stats['total_expenditures'], 2),
            'budget_utilization' => number_format($stats['budget_utilization'], 1) . '%',
            'average_completion' => number_format($stats['average_completion'], 1) . '%',
        ];
    }

    /**
     * Get color with opacity for backgrounds
     *
     * @param float $opacity
     * @return string
     */
    public function getColorWithOpacity(float $opacity = 0.1): string
    {
        // Convert hex to rgba
        $hex = str_replace('#', '', $this->color);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r}, {$g}, {$b}, {$opacity})";
    }

    /**
     * Get contrasting text color (black or white) based on background
     *
     * @return string
     */
    public function getContrastingTextColor(): string
    {
        // Convert hex to RGB
        $hex = str_replace('#', '', $this->color);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Calculate luminance
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5 ? '#000000' : '#FFFFFF';
    }
}
