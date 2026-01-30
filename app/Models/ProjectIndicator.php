<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ProjectIndicator Model
 * 
 * Manages the relationship between projects and indicators in the KMC M&E System
 * 
 * @property int $id
 * @property int $project_id
 * @property int $indicator_id
 * @property float|null $baseline_value
 * @property float|null $target_value
 * @property float $current_value
 * @property float $achievement_percentage
 * @property string $reporting_frequency
 * @property string|null $notes
 * @property int|null $updated_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class ProjectIndicator extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'indicator_id',
        'baseline_value',
        'target_value',
        'current_value',
        'achievement_percentage',
        'reporting_frequency',
        'notes',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
    ];

    /**
     * Frequency constants
     */
    const FREQUENCY_MONTHLY = 'MONTHLY';
    const FREQUENCY_QUARTERLY = 'QUARTERLY';
    const FREQUENCY_ANNUALLY = 'ANNUALLY';
    const FREQUENCY_ADHOC = 'ADHOC';

    /**
     * Get all possible frequencies
     */
    public static function frequencies(): array
    {
        return [
            self::FREQUENCY_MONTHLY,
            self::FREQUENCY_QUARTERLY,
            self::FREQUENCY_ANNUALLY,
            self::FREQUENCY_ADHOC,
        ];
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the project that owns the project indicator
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the indicator that owns the project indicator
     */
    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }

    /**
     * Get the user who last updated the project indicator
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the data entries for this project indicator
     */
    public function dataEntries()
    {
        return $this->hasMany(DataEntry::class);
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
     * Scope a query to filter by reporting frequency
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $frequency
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFrequency($query, string $frequency)
    {
        return $query->where('reporting_frequency', $frequency);
    }

    /**
     * Scope a query to include indicators with low achievement
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $threshold
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowAchievement($query, float $threshold = 50.0)
    {
        return $query->where('achievement_percentage', '<', $threshold);
    }

    /**
     * Scope a query to include indicators with high achievement
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $threshold
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighAchievement($query, float $threshold = 80.0)
    {
        return $query->where('achievement_percentage', '>=', $threshold);
    }

    // ========================================================================
    // CALCULATED ATTRIBUTES
    // ========================================================================

    /**
     * Get the frequency label
     *
     * @return string
     */
    public function getFrequencyLabelAttribute(): string
    {
        return [
            self::FREQUENCY_MONTHLY => 'Monthly',
            self::FREQUENCY_QUARTERLY => 'Quarterly',
            self::FREQUENCY_ANNUALLY => 'Annually',
            self::FREQUENCY_ADHOC => 'Ad-hoc',
        ][$this->reporting_frequency] ?? $this->reporting_frequency;
    }

    /**
     * Get the achievement status
     *
     * @return string
     */
    public function getAchievementStatusAttribute(): string
    {
        if ($this->achievement_percentage >= 90) return 'excellent';
        if ($this->achievement_percentage >= 70) return 'good';
        if ($this->achievement_percentage >= 50) return 'needs_attention';
        return 'critical';
    }

    /**
     * Get the achievement status color
     *
     * @return string
     */
    public function getAchievementStatusColorAttribute(): string
    {
        return [
            'excellent' => 'success',
            'good' => 'info',
            'needs_attention' => 'warning',
            'critical' => 'danger',
        ][$this->achievement_status] ?? 'secondary';
    }

    /**
     * Check if indicator is on track
     *
     * @return bool
     */
    public function getIsOnTrackAttribute(): bool
    {
        return $this->achievement_percentage >= 70;
    }

    /**
     * Get the progress towards target
     *
     * @return float
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return 0;
        }

        return min(($this->current_value / $this->target_value) * 100, 100);
    }

    /**
     * Get the remaining value to reach target
     *
     * @return float
     */
    public function getRemainingValueAttribute(): float
    {
        if (!$this->target_value) {
            return 0;
        }

        return max(0, $this->target_value - $this->current_value);
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Update the current value and recalculate achievement
     *
     * @param float $newValue
     * @param int|null $updatedBy
     * @param string|null $notes
     * @return bool
     */
    public function updateValue(float $newValue, ?int $updatedBy = null, ?string $notes = null): bool
    {
        $this->current_value = $newValue;
        $this->updated_by = $updatedBy ?? auth()->id();
        
        if ($notes) {
            $this->notes = $notes;
        }

        $this->recalculateAchievement();
        
        return $this->save();
    }

    /**
     * Recalculate achievement percentage based on current value
     *
     * @return void
     */
    public function recalculateAchievement(): void
    {
        if (!$this->target_value || $this->target_value == 0) {
            $this->achievement_percentage = 0;
            return;
        }

        $achievement = ($this->current_value / $this->target_value) * 100;
        $this->achievement_percentage = min($achievement, 100);
    }

    /**
     * Get formatted values with units
     *
     * @return array
     */
    public function getFormattedValues(): array
    {
        $unit = $this->indicator?->unit_label ?? '';
        
        return [
            'baseline_value' => $this->baseline_value !== null 
                ? number_format($this->baseline_value, 2) . ' ' . $unit 
                : 'N/A',
            'target_value' => $this->target_value !== null 
                ? number_format($this->target_value, 2) . ' ' . $unit 
                : 'N/A',
            'current_value' => number_format($this->current_value, 2) . ' ' . $unit,
            'achievement_percentage' => number_format($this->achievement_percentage, 1) . '%',
            'progress_percentage' => number_format($this->progress_percentage, 1) . '%',
            'remaining_value' => number_format($this->remaining_value, 2) . ' ' . $unit,
        ];
    }

    /**
     * Get the latest data entry
     *
     * @return DataEntry|null
     */
    public function getLatestDataEntry(): ?DataEntry
    {
        return $this->dataEntries()->latest()->first();
    }

    /**
     * Get data entries within date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDataEntriesInRange(string $startDate, string $endDate)
    {
        return $this->dataEntries()
                    ->whereBetween('data_date', [$startDate, $endDate])
                    ->orderBy('data_date')
                    ->get();
    }

    /**
     * Check if reporting is due
     *
     * @return bool
     */
    public function isReportingDue(): bool
    {
        $latestEntry = $this->getLatestDataEntry();
        
        if (!$latestEntry) {
            return true;
        }

        $now = now();
        $lastReportDate = $latestEntry->data_date;

        switch ($this->reporting_frequency) {
            case self::FREQUENCY_MONTHLY:
                return $now->diffInMonths($lastReportDate) >= 1;
            
            case self::FREQUENCY_QUARTERLY:
                return $now->diffInMonths($lastReportDate) >= 3;
            
            case self::FREQUENCY_ANNUALLY:
                return $now->diffInYears($lastReportDate) >= 1;
            
            case self::FREQUENCY_ADHOC:
                return false; // No due date for ad-hoc reporting
            
            default:
                return false;
        }
    }

    /**
     * Get next reporting due date
     *
     * @return \Carbon\Carbon|null
     */
    public function getNextReportingDate(): ?\Carbon\Carbon
    {
        $latestEntry = $this->getLatestDataEntry();
        
        if (!$latestEntry) {
            return now();
        }

        $lastReportDate = $latestEntry->data_date;

        switch ($this->reporting_frequency) {
            case self::FREQUENCY_MONTHLY:
                return $lastReportDate->addMonth();
            
            case self::FREQUENCY_QUARTERLY:
                return $lastReportDate->addMonths(3);
            
            case self::FREQUENCY_ANNUALLY:
                return $lastReportDate->addYear();
            
            default:
                return null;
        }
    }

    /**
     * Get project indicator summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        $formattedValues = $this->getFormattedValues();

        return [
            'id' => $this->id,
            'project_name' => $this->project?->name,
            'indicator_name' => $this->indicator?->name,
            'indicator_code' => $this->indicator?->code,
            'indicator_type' => $this->indicator?->type,
            'indicator_unit' => $this->indicator?->unit_label,
            'baseline_value' => $formattedValues['baseline_value'],
            'target_value' => $formattedValues['target_value'],
            'current_value' => $formattedValues['current_value'],
            'achievement_percentage' => $formattedValues['achievement_percentage'],
            'progress_percentage' => $formattedValues['progress_percentage'],
            'remaining_value' => $formattedValues['remaining_value'],
            'achievement_status' => $this->achievement_status,
            'achievement_status_color' => $this->achievement_status_color,
            'is_on_track' => $this->is_on_track,
            'reporting_frequency' => $this->reporting_frequency,
            'frequency_label' => $this->frequency_label,
            'is_reporting_due' => $this->isReportingDue(),
            'next_reporting_date' => $this->getNextReportingDate()?->format('Y-m-d'),
            'notes' => $this->notes,
            'updated_by' => $this->updater?->full_name,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check if user can update this project indicator
     *
     * @param User $user
     * @return bool
     */
    public function canUserUpdate(User $user): bool
    {
        return $user->hasPermission('edit_data') || 
               $user->hasPermission('manage_project_indicators') ||
               $this->project->created_by === $user->id;
    }
}
