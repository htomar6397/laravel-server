<?php

namespace App\Models;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 * 
 * Handles user authentication, authorization, and relationships
 * for the KMC M&E System
 * 
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $full_name
 * @property int|null $org_unit_id
 * @property string|null $department
 * @property string|null $position
 * @property string|null $phone
 * @property bool $is_active
 * @property \Carbon\Carbon|null $last_login_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'org_unit_id',
        'department',
        'position',
        'phone',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Log user creation
        static::created(function ($user) {
            $request = app()->runningInConsole() ? null : request();
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'USER_CREATED',
                'entity_type' => 'User',
                'entity_id' => $user->id,
                'new_values' => $user->toArray(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        });

        // Log user updates
        static::updated(function ($user) {
            $request = app()->runningInConsole() ? null : request();
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'USER_UPDATED',
                'entity_type' => 'User',
                'entity_id' => $user->id,
                'old_values' => $user->getOriginal(),
                'new_values' => $user->getChanges(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        });
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the organizational unit that the user belongs to
     */
    public function organizationalUnit()
    {
        return $this->belongsTo(OrganizationalUnit::class, 'org_unit_id');
    }

    /**
     * Get the roles assigned to the user
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot('assigned_at', 'assigned_by')
                    ->withTimestamps();
    }

    /**
     * Get the projects created by the user
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    /**
     * Get the data entries created by the user
     */
    public function dataEntries()
    {
        return $this->hasMany(DataEntry::class, 'entered_by');
    }

    /**
     * Get the expenditures entered by the user
     */
    public function expenditures()
    {
        return $this->hasMany(Expenditure::class, 'entered_by');
    }

    /**
     * Get the photo captures taken by the user
     */
    public function photoCaptures()
    {
        return $this->hasMany(PhotoCapture::class, 'captured_by');
    }

    /**
     * Get the notifications for the user
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications for the user
     */
    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    /**
     * Get the audit logs for the user
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get users supervised by this user (if manager)
     */
    public function supervisedUsers()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Get the supervisor of this user
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    // ========================================================================
    // AUTHORIZATION HELPER METHODS
    // ========================================================================

    /**
     * Check if user has a specific role
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the given roles
     *
     * @param array $roleNames
     * @return bool
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if user has all of the given roles
     *
     * @param array $roleNames
     * @return bool
     */
    public function hasAllRoles(array $roleNames): bool
    {
        $userRoles = $this->roles()->pluck('name')->toArray();
        return count(array_intersect($roleNames, $userRoles)) === count($roleNames);
    }

    /**
     * Check if user has a specific permission
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->where(function($query) use ($permission) {
                $query->whereJsonContains('permissions', $permission)
                      ->orWhereJsonContains('permissions', '*');
            })->exists();
    }

    /**
     * Check if user has any of the given permissions
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
     * Check if user is a system administrator
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('System Administrator');
    }

    /**
     * Check if user can approve expenditures
     *
     * @return bool
     */
    public function canApproveExpenditures(): bool
    {
        return $this->hasAnyPermission(['approve_expenditures', '*']);
    }

    /**
     * Check if user can enter data
     *
     * @return bool
     */
    public function canEnterData(): bool
    {
        return $this->hasAnyPermission(['enter_data', '*']);
    }

    /**
     * Check if user can view reports
     *
     * @return bool
     */
    public function canViewReports(): bool
    {
        return $this->hasAnyPermission(['view_reports', '*']);
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Get user's full name with position
     *
     * @return string
     */
    public function getFullNameWithPositionAttribute(): string
    {
        if ($this->position) {
            return "{$this->full_name} ({$this->position})";
        }
        return $this->full_name;
    }

    /**
     * Get user's initials
     *
     * @return string
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->full_name);
        $initials = '';
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        return $initials;
    }

    /**
     * Update last login timestamp
     *
     * @return void
     */
    public function updateLastLogin(): void
    {
        $this->last_login_at = now();
        $this->save();
    }

    /**
     * Activate user account
     *
     * @return bool
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate user account
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Assign a role to the user
     *
     * @param int|Role $role
     * @param int|null $assignedBy
     * @return void
     */
    public function assignRole($role, ?int $assignedBy = null): void
    {
        $roleId = $role instanceof Role ? $role->id : $role;
        
        $this->roles()->syncWithoutDetaching([
            $roleId => [
                'assigned_by' => $assignedBy ?? auth()->id(),
                'assigned_at' => now(),
            ]
        ]);
    }

    /**
     * Remove a role from the user
     *
     * @param int|Role $role
     * @return void
     */
    public function removeRole($role): void
    {
        $roleId = $role instanceof Role ? $role->id : $role;
        $this->roles()->detach($roleId);
    }

    /**
     * Sync user roles
     *
     * @param array $roleIds
     * @return void
     */
    public function syncRoles(array $roleIds): void
    {
        $syncData = [];
        foreach ($roleIds as $roleId) {
            $syncData[$roleId] = [
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ];
        }
        $this->roles()->sync($syncData);
    }

    /**
     * Get all permissions for the user (from all roles)
     *
     * @return array
     */
    public function getAllPermissions(): array
    {
        $permissions = [];
        foreach ($this->roles as $role) {
            if ($role->permissions) {
                $rolePermissions = is_array($role->permissions) 
                    ? $role->permissions 
                    : json_decode($role->permissions, true) ?? [];
                $permissions = array_merge($permissions, $rolePermissions);
            }
        }
        return array_unique($permissions);
    }

    /**
     * Get user statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'projects_created' => $this->projects()->count(),
            'expenditures_entered' => $this->expenditures()->count(),
            'photos_captured' => $this->photoCaptures()->count(),
            'total_expenditure_amount' => $this->expenditures()->sum('amount'),
            'unread_notifications' => $this->unreadNotifications()->count(),
            'last_login' => $this->last_login_at?->diffForHumans(),
            'account_age_days' => $this->created_at->diffInDays(now()),
        ];
    }

    /**
     * Check if user belongs to a specific organizational unit
     *
     * @param int $orgUnitId
     * @return bool
     */
    public function belongsToOrgUnit(int $orgUnitId): bool
    {
        return $this->org_unit_id === $orgUnitId;
    }

    /**
     * Check if user belongs to any child organizational unit
     *
     * @param int $parentOrgUnitId
     * @return bool
     */
    public function belongsToChildOrgUnit(int $parentOrgUnitId): bool
    {
        if (!$this->organizationalUnit) {
            return false;
        }

        $parent = $this->organizationalUnit->parent;
        while ($parent) {
            if ($parent->id === $parentOrgUnitId) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to only include active users
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive users
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to filter by organizational unit
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $orgUnitId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByOrgUnit($query, int $orgUnitId)
    {
        return $query->where('org_unit_id', $orgUnitId);
    }

    /**
     * Scope a query to filter by role
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $roleName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRole($query, string $roleName)
    {
        return $query->whereHas('roles', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope a query to search users
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('position', 'like', "%{$search}%");
        });
    }
}
