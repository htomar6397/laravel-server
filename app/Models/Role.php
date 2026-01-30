<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Role Model
 * 
 * Manages system roles and permissions for the KMC M&E System
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property array|null $permissions
 * @property bool $is_system
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'permissions',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the users that have this role
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')
                    ->withPivot('assigned_at', 'assigned_by')
                    ->withTimestamps();
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to only include system roles
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope a query to only include custom roles
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope a query to search roles
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Check if role has a specific permission
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array('*', $this->permissions) || in_array($permission, $this->permissions);
    }

    /**
     * Check if role has any of the given permissions
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all permissions as a flat array
     *
     * @return array
     */
    public function getAllPermissions(): array
    {
        return $this->permissions ?? [];
    }

    /**
     * Add a permission to the role
     *
     * @param string $permission
     * @return void
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Remove a permission from the role
     *
     * @param string $permission
     * @return void
     */
    public function removePermission(string $permission): void
    {
        if (!$this->permissions) {
            return;
        }

        $permissions = $this->permissions;
        $key = array_search($permission, $permissions);
        
        if ($key !== false) {
            unset($permissions[$key]);
            $this->permissions = array_values($permissions);
            $this->save();
        }
    }

    /**
     * Sync permissions for the role
     *
     * @param array $permissions
     * @return void
     */
    public function syncPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
        $this->save();
    }

    /**
     * Get the count of users with this role
     *
     * @return int
     */
    public function getUserCount(): int
    {
        return $this->users()->count();
    }

    /**
     * Check if this role can be deleted (not system and no users)
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return !$this->is_system && $this->getUserCount() === 0;
    }

    /**
     * Get role summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_system' => $this->is_system,
            'user_count' => $this->getUserCount(),
            'permissions_count' => count($this->permissions ?? []),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
