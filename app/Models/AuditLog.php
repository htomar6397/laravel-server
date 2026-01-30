<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AuditLog Model
 * 
 * Manages audit logs for the KMC M&E System
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string $entity_type
 * @property int|null $entity_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class AuditLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Disable timestamps (we only need created_at)
     */
    public $timestamps = ['created_at'];

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the user that performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to filter by action
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to filter by entity type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $entityType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope a query to filter by user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to include recent logs
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to search logs
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('action', 'like', "%{$search}%")
              ->orWhere('entity_type', 'like', "%{$search}%")
              ->orWhere('ip_address', 'like', "%{$search}%")
              ->orWhereHas('user', function ($userQuery) use ($search) {
                  $userQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
              });
        });
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Get the action label
     *
     * @return string
     */
    public function getActionLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', strtolower($this->action)));
    }

    /**
     * Get the entity type label
     *
     * @return string
     */
    public function getEntityTypeLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', strtolower($this->entity_type)));
    }

    /**
     * Get time ago format
     *
     * @return string
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the changes made
     *
     * @return array
     */
    public function getChangesAttribute(): array
    {
        $changes = [];

        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                
                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        } elseif ($this->new_values) {
            // Creation
            $changes = $this->new_values;
        } elseif ($this->old_values) {
            // Deletion
            $changes = $this->old_values;
        }

        return $changes;
    }

    /**
     * Get the action type (CREATE, UPDATE, DELETE)
     *
     * @return string
     */
    public function getActionTypeAttribute(): string
    {
        if (str_ends_with($this->action, '_CREATED')) {
            return 'CREATE';
        } elseif (str_ends_with($this->action, '_UPDATED')) {
            return 'UPDATE';
        } elseif (str_ends_with($this->action, '_DELETED')) {
            return 'DELETE';
        } else {
            return 'OTHER';
        }
    }

    /**
     * Get the action type color for UI
     *
     * @return string
     */
    public function getActionTypeColorAttribute(): string
    {
        return [
            'CREATE' => 'success',
            'UPDATE' => 'info',
            'DELETE' => 'danger',
            'OTHER' => 'secondary',
        ][$this->action_type] ?? 'secondary';
    }

    /**
     * Get the action type icon
     *
     * @return string
     */
    public function getActionTypeIconAttribute(): string
    {
        return [
            'CREATE' => 'fas fa-plus',
            'UPDATE' => 'fas fa-edit',
            'DELETE' => 'fas fa-trash',
            'OTHER' => 'fas fa-cog',
        ][$this->action_type] ?? 'fas fa-cog';
    }

    /**
     * Get formatted user agent
     *
     * @return string
     */
    public function getFormattedUserAgentAttribute(): string
    {
        if (!$this->user_agent) {
            return 'Unknown';
        }

        // Parse user agent to get browser and OS
        $userAgent = $this->user_agent;
        
        if (preg_match('/Chrome\/[\d.]+/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\/[\d.]+/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\/[\d.]+/', $userAgent)) {
            $browser = 'Safari';
        } else {
            $browser = 'Unknown';
        }

        if (preg_match('/Windows/', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac/', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iOS/', $userAgent)) {
            $os = 'iOS';
        } else {
            $os = 'Unknown';
        }

        return "{$browser} on {$os}";
    }

    /**
     * Log an action
     *
     * @param string $action
     * @param string $entityType
     * @param int|null $entityId
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param int|null $userId
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return static
     */
    public static function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): static {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);
    }

    /**
     * Get audit log summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->full_name,
                'username' => $this->user->username,
            ] : null,
            'action' => $this->action,
            'action_label' => $this->action_label,
            'action_type' => $this->action_type,
            'action_type_color' => $this->action_type_color,
            'action_type_icon' => $this->action_type_icon,
            'entity_type' => $this->entity_type,
            'entity_type_label' => $this->entity_type_label,
            'entity_id' => $this->entity_id,
            'changes' => $this->changes,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->formatted_user_agent,
            'time_ago' => $this->time_ago,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Clean up old audit logs
     *
     * @param int $daysToKeep
     * @return int
     */
    public static function cleanupOldLogs(int $daysToKeep = 365): int
    {
        return static::where('created_at', '<', now()->subDays($daysToKeep))->delete();
    }
}
