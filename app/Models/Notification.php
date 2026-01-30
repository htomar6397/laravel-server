<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Notification Model
 * 
 * Manages user notifications for the KMC M&E System
 * 
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $message
 * @property string $type
 * @property string|null $category
 * @property string|null $action_url
 * @property array|null $metadata
 * @property bool $is_read
 * @property \Carbon\Carbon|null $read_at
 * @property bool $is_email_sent
 * @property \Carbon\Carbon|null $email_sent_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'category',
        'action_url',
        'metadata',
        'is_read',
        'read_at',
        'is_email_sent',
        'email_sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    /**
     * Type constants
     */
    const TYPE_INFO = 'INFO';
    const TYPE_SUCCESS = 'SUCCESS';
    const TYPE_WARNING = 'WARNING';
    const TYPE_ERROR = 'ERROR';
    const TYPE_ALERT = 'ALERT';

    /**
     * Get all possible types
     */
    public static function types(): array
    {
        return [
            self::TYPE_INFO,
            self::TYPE_SUCCESS,
            self::TYPE_WARNING,
            self::TYPE_ERROR,
            self::TYPE_ALERT,
        ];
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to only include unread notifications
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to filter by type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by category
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to include recent notifications
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Mark notification as read
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        $this->is_read = true;
        $this->read_at = now();

        return $this->save();
    }

    /**
     * Mark notification as unread
     *
     * @return bool
     */
    public function markAsUnread(): bool
    {
        if (!$this->is_read) {
            return true;
        }

        $this->is_read = false;
        $this->read_at = null;

        return $this->save();
    }

    /**
     * Mark email as sent
     *
     * @return bool
     */
    public function markEmailAsSent(): bool
    {
        if ($this->is_email_sent) {
            return true;
        }

        $this->is_email_sent = true;
        $this->email_sent_at = now();

        return $this->save();
    }

    /**
     * Get the type label
     *
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return [
            self::TYPE_INFO => 'Info',
            self::TYPE_SUCCESS => 'Success',
            self::TYPE_WARNING => 'Warning',
            self::TYPE_ERROR => 'Error',
            self::TYPE_ALERT => 'Alert',
        ][$this->type] ?? $this->type;
    }

    /**
     * Get the type color for UI
     *
     * @return string
     */
    public function getTypeColorAttribute(): string
    {
        return [
            self::TYPE_INFO => 'info',
            self::TYPE_SUCCESS => 'success',
            self::TYPE_WARNING => 'warning',
            self::TYPE_ERROR => 'danger',
            self::TYPE_ALERT => 'primary',
        ][$this->type] ?? 'secondary';
    }

    /**
     * Get the type icon
     *
     * @return string
     */
    public function getTypeIconAttribute(): string
    {
        return [
            self::TYPE_INFO => 'fas fa-info-circle',
            self::TYPE_SUCCESS => 'fas fa-check-circle',
            self::TYPE_WARNING => 'fas fa-exclamation-triangle',
            self::TYPE_ERROR => 'fas fa-times-circle',
            self::TYPE_ALERT => 'fas fa-bell',
        ][$this->type] ?? 'fas fa-bell';
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
     * Create a new notification
     *
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param string|null $category
     * @param string|null $actionUrl
     * @param array|null $metadata
     * @return static
     */
    public static function createNotification(
        int $userId,
        string $title,
        string $message,
        string $type = self::TYPE_INFO,
        ?string $category = null,
        ?string $actionUrl = null,
        ?array $metadata = null
    ): static {
        return static::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'category' => $category,
            'action_url' => $actionUrl,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create project-related notification
     *
     * @param int $userId
     * @param Project $project
     * @param string $action
     * @param string $type
     * @return static
     */
    public static function createProjectNotification(
        int $userId,
        Project $project,
        string $action,
        string $type = self::TYPE_INFO
    ): static {
        $title = "Project {$action}";
        $message = "Project '{$project->name}' has been {$action}.";
        $actionUrl = route('projects.show', $project);

        return static::createNotification(
            $userId,
            $title,
            $message,
            $type,
            'PROJECT',
            $actionUrl,
            ['project_id' => $project->id]
        );
    }

    /**
     * Create expenditure notification
     *
     * @param int $userId
     * @param Expenditure $expenditure
     * @param string $action
     * @param string $type
     * @return static
     */
    public static function createExpenditureNotification(
        int $userId,
        Expenditure $expenditure,
        string $action,
        string $type = self::TYPE_INFO
    ): static {
        $title = "Expenditure {$action}";
        $message = "Expenditure '{$expenditure->description}' has been {$action}.";
        $actionUrl = route('expenditures.show', $expenditure);

        return static::createNotification(
            $userId,
            $title,
            $message,
            $type,
            'EXPENDITURE',
            $actionUrl,
            ['expenditure_id' => $expenditure->id]
        );
    }

    /**
     * Get notification summary for API
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'type_color' => $this->type_color,
            'type_icon' => $this->type_icon,
            'category' => $this->category,
            'action_url' => $this->action_url,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->format('Y-m-d H:i:s'),
            'time_ago' => $this->time_ago,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
