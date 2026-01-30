<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ProjectResult Model
 * 
 * Manages project results and achievements in the KMC M&E System
 * 
 * @property int $id
 * @property int $project_id
 * @property int|null $indicator_id
 * @property string $title
 * @property string|null $description
 * @property string $type
 * @property float|null $target_value
 * @property float $achieved_value
 * @property string|null $unit
 * @property \Carbon\Carbon|null $target_date
 * @property \Carbon\Carbon|null $achieved_date
 * @property string $status
 * @property string|null $evidence
 * @property string|null $challenges
 * @property string|null $lessons_learned
 * @property int $reported_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class ProjectResult extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'indicator_id',
        'title',
        'description',
        'type',
        'target_value',
        'achieved_value',
        'unit',
        'target_date',
        'achieved_date',
        'status',
        'evidence',
        'challenges',
        'lessons_learned',
        'reported_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'target_value' => 'decimal:2',
        'achieved_value' => 'decimal:2',
        'target_date' => 'date',
        'achieved_date' => 'date',
    ];

    /**
     * Type constants
     */
    const TYPE_OUTPUT = 'OUTPUT';
    const TYPE_OUTCOME = 'OUTCOME';
    const TYPE_IMPACT = 'IMPACT';

    /**
     * Status constants
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_ACHIEVED = 'ACHIEVED';
    const STATUS_PARTIALLY_ACHIEVED = 'PARTIALLY_ACHIEVED';
    const STATUS_NOT_ACHIEVED = 'NOT_ACHIEVED';

    /**
     * Get all possible types
     */
    public static function types(): array
    {
        return [
            self::TYPE_OUTPUT,
            self::TYPE_OUTCOME,
            self::TYPE_IMPACT,
        ];
    }

    /**
     * Get all possible statuses
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACHIEVED,
            self::STATUS_PARTIALLY_ACHIEVED,
            self::STATUS_NOT_ACHIEVED,
        ];
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the project that owns the result
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the indicator that owns the result
     */
    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }

    /**
     * Get the user who reported the result
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get all file attachments for the result
     */
    public function fileAttachments()
    {
        return $this->morphMany(FileAttachment::class, 'entity');
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to filter by project
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope a query to filter by indicator
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $indicatorId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByIndicator($query, int $indicatorId)
    {
        return $query->where('indicator_id', $indicatorId);
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
     * Scope a query to filter by status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to include achieved results
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAchieved($query)
    {
        return $query->where('status', self::STATUS_ACHIEVED);
    }

    /**
     * Scope a query to include results achieved in date range
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAchievedInRange($query, string $startDate, string $endDate)
    {
        return $query->whereNotNull('achieved_date')
                    ->whereBetween('achieved_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to search results
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('evidence', 'like', "%{$search}%")
              ->orWhere('challenges', 'like', "%{$search}%")
              ->orWhere('lessons_learned', 'like', "%{$search}%");
        });
    }

    // ========================================================================
    // CALCULATED ATTRIBUTES
    // ========================================================================

    /**
     * Get the type label
     *
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return [
            self::TYPE_OUTPUT => 'Output',
            self::TYPE_OUTCOME => 'Outcome',
            self::TYPE_IMPACT => 'Impact',
        ][$this->type] ?? $this->type;
    }

    /**
     * Get the status label
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_ACHIEVED => 'Achieved',
            self::STATUS_PARTIALLY_ACHIEVED => 'Partially Achieved',
            self::STATUS_NOT_ACHIEVED => 'Not Achieved',
        ][$this->status] ?? $this->status;
    }

    /**
     * Get the status color for UI
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return [
            self::STATUS_PENDING => 'warning',
            self::STATUS_ACHIEVED => 'success',
            self::STATUS_PARTIALLY_ACHIEVED => 'info',
            self::STATUS_NOT_ACHIEVED => 'danger',
        ][$this->status] ?? 'secondary';
    }

    /**
     * Get the achievement percentage
     *
     * @return float
     */
    public function getAchievementPercentageAttribute(): float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return 0;
        }

        return min(($this->achieved_value / $this->target_value) * 100, 100);
    }

    /**
     * Check if result is overdue
     *
     * @return bool
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->target_date || $this->status === self::STATUS_ACHIEVED) {
            return false;
        }

        return now()->isAfter($this->target_date);
    }

    /**
     * Get days overdue
     *
     * @return int|null
     */
    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->is_overdue) {
            return null;
        }

        return now()->diffInDays($this->target_date);
    }

    /**
     * Get formatted values with units
     *
     * @return array
     */
    public function getFormattedValuesAttribute(): array
    {
        $unit = $this->unit ?? '';
        
        return [
            'target_value' => $this->target_value !== null 
                ? number_format($this->target_value, 2) . ' ' . $unit 
                : 'N/A',
            'achieved_value' => number_format($this->achieved_value, 2) . ' ' . $unit,
            'achievement_percentage' => number_format($this->achievement_percentage, 1) . '%',
        ];
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Mark result as achieved
     *
     * @param float $achievedValue
     * @param \Carbon\Carbon|null $achievedDate
     * @param string|null $evidence
     * @return bool
     */
    public function markAsAchieved(
        float $achievedValue, 
        ?\Carbon\Carbon $achievedDate = null, 
        ?string $evidence = null
    ): bool {
        $this->achieved_value = $achievedValue;
        $this->achieved_date = $achievedDate ?? now();
        
        if ($evidence) {
            $this->evidence = $evidence;
        }

        // Determine status based on achievement
        if ($this->target_value && $this->target_value > 0) {
            $achievement = ($achievedValue / $this->target_value) * 100;
            
            if ($achievement >= 100) {
                $this->status = self::STATUS_ACHIEVED;
            } elseif ($achievement >= 50) {
                $this->status = self::STATUS_PARTIALLY_ACHIEVED;
            } else {
                $this->status = self::STATUS_NOT_ACHIEVED;
            }
        } else {
            $this->status = self::STATUS_ACHIEVED;
        }

        return $this->save();
    }

    /**
     * Update result status
     *
     * @param string $status
     * @return bool
     */
    public function updateStatus(string $status): bool
    {
        if (!in_array($status, self::statuses())) {
            return false;
        }

        $this->status = $status;
        
        if ($status === self::STATUS_ACHIEVED && !$this->achieved_date) {
            $this->achieved_date = now();
        }

        return $this->save();
    }

    /**
     * Get result summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        $formattedValues = $this->formatted_values;

        return [
            'id' => $this->id,
            'project_name' => $this->project?->name,
            'indicator_name' => $this->indicator?->name,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'target_value' => $formattedValues['target_value'],
            'achieved_value' => $formattedValues['achieved_value'],
            'achievement_percentage' => $formattedValues['achievement_percentage'],
            'unit' => $this->unit,
            'target_date' => $this->target_date?->format('Y-m-d'),
            'achieved_date' => $this->achieved_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'is_overdue' => $this->is_overdue,
            'days_overdue' => $this->days_overdue,
            'evidence' => $this->evidence,
            'challenges' => $this->challenges,
            'lessons_learned' => $this->lessons_learned,
            'reported_by' => $this->reporter?->full_name,
            'attachments_count' => $this->fileAttachments()->count(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check if user can update this result
     *
     * @param User $user
     * @return bool
     */
    public function canUserUpdate(User $user): bool
    {
        return $user->hasPermission('edit_results') || 
               $this->project->created_by === $user->id ||
               $this->reported_by === $user->id;
    }

    /**
     * Check if user can delete this result
     *
     * @param User $user
     * @return bool
     */
    public function canUserDelete(User $user): bool
    {
        return $user->hasPermission('delete_results') || 
               $this->project->created_by === $user->id ||
               $this->reported_by === $user->id;
    }

    /**
     * Get result statistics for a project
     *
     * @param int $projectId
     * @return array
     */
    public static function getProjectStats(int $projectId): array
    {
        $results = static::byProject($projectId);
        
        return [
            'total' => $results->count(),
            'achieved' => $results->achieved()->count(),
            'partially_achieved' => $results->byStatus(self::STATUS_PARTIALLY_ACHIEVED)->count(),
            'not_achieved' => $results->byStatus(self::STATUS_NOT_ACHIEVED)->count(),
            'pending' => $results->byStatus(self::STATUS_PENDING)->count(),
            'overdue' => $results->where('is_overdue', true)->count(),
        ];
    }

    /**
     * Get result statistics by type
     *
     * @param int|null $projectId
     * @return array
     */
    public static function getStatsByType(?int $projectId = null): array
    {
        $query = static::query();
        
        if ($projectId) {
            $query->byProject($projectId);
        }

        return [
            'outputs' => $query->byType(self::TYPE_OUTPUT)->count(),
            'outcomes' => $query->byType(self::TYPE_OUTCOME)->count(),
            'impacts' => $query->byType(self::TYPE_IMPACT)->count(),
        ];
    }
}
