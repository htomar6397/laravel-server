<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Indicator Model
 * 
 * Manages performance indicators for the KMC M&E System
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int|null $theme_id
 * @property string $type
 * @property string $unit
 * @property string|null $unit_label
 * @property float|null $baseline_value
 * @property float|null $target_value
 * @property string $direction
 * @property string $frequency
 * @property string|null $data_source
 * @property string|null $calculation_method
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Indicator extends Model
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
        'theme_id',
        'type',
        'unit',
        'unit_label',
        'baseline_value',
        'target_value',
        'direction',
        'frequency',
        'data_source',
        'calculation_method',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Indicator type constants
     */
    const TYPE_QUANTITATIVE = 'QUANTITATIVE';
    const TYPE_QUALITATIVE = 'QUALITATIVE';

    /**
     * Unit constants
     */
    const UNIT_NUMBER = 'NUMBER';
    const UNIT_PERCENTAGE = 'PERCENTAGE';
    const UNIT_CURRENCY = 'CURRENCY';
    const UNIT_RATIO = 'RATIO';
    const UNIT_RATE = 'RATE';
    const UNIT_COUNT = 'COUNT';
    const UNIT_YES_NO = 'YES_NO';

    /**
     * Direction constants
     */
    const DIRECTION_INCREASE = 'INCREASE';
    const DIRECTION_DECREASE = 'DECREASE';
    const DIRECTION_MAINTAIN = 'MAINTAIN';

    /**
     * Frequency constants
     */
    const FREQUENCY_MONTHLY = 'MONTHLY';
    const FREQUENCY_QUARTERLY = 'QUARTERLY';
    const FREQUENCY_ANNUALLY = 'ANNUALLY';
    const FREQUENCY_ADHOC = 'ADHOC';

    /**
     * Get all possible indicator types
     */
    public static function types(): array
    {
        return [
            self::TYPE_QUANTITATIVE,
            self::TYPE_QUALITATIVE,
        ];
    }

    /**
     * Get all possible units
     */
    public static function units(): array
    {
        return [
            self::UNIT_NUMBER,
            self::UNIT_PERCENTAGE,
            self::UNIT_CURRENCY,
            self::UNIT_RATIO,
            self::UNIT_RATE,
            self::UNIT_COUNT,
            self::UNIT_YES_NO,
        ];
    }

    /**
     * Get all possible directions
     */
    public static function directions(): array
    {
        return [
            self::DIRECTION_INCREASE,
            self::DIRECTION_DECREASE,
            self::DIRECTION_MAINTAIN,
        ];
    }

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
     * Get the theme that owns the indicator
     */
    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Get the project indicators for this indicator
     */
    public function projectIndicators()
    {
        return $this->hasMany(ProjectIndicator::class);
    }

    /**
     * Get the projects through project_indicators pivot
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_indicators')
                    ->withPivot([
                        'baseline_value',
                        'target_value',
                        'current_value',
                        'achievement_percentage',
                        'reporting_frequency',
                    ])
                    ->withTimestamps();
    }

    /**
     * Get the data entries for this indicator
     */
    public function dataEntries()
    {
        return $this->hasMany(DataEntry::class);
    }

    /**
     * Get the project results for this indicator
     */
    public function projectResults()
    {
        return $this->hasMany(ProjectResult::class);
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to only include active indicators
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
     * Scope a query to filter by frequency
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $frequency
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFrequency($query, string $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope a query to filter by theme
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $themeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTheme($query, int $themeId)
    {
        return $query->where('theme_id', $themeId);
    }

    /**
     * Scope a query to search indicators
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
            self::TYPE_QUANTITATIVE => 'Quantitative',
            self::TYPE_QUALITATIVE => 'Qualitative',
        ][$this->type] ?? $this->type;
    }

    /**
     * Get the unit label
     *
     * @return string
     */
    public function getUnitLabelAttribute(): string
    {
        return $this->unit_label ?? [
            self::UNIT_NUMBER => 'Number',
            self::UNIT_PERCENTAGE => '%',
            self::UNIT_CURRENCY => 'Currency',
            self::UNIT_RATIO => 'Ratio',
            self::UNIT_RATE => 'Rate',
            self::UNIT_COUNT => 'Count',
            self::UNIT_YES_NO => 'Yes/No',
        ][$this->unit] ?? $this->unit;
    }

    /**
     * Get the direction label
     *
     * @return string
     */
    public function getDirectionLabelAttribute(): string
    {
        return [
            self::DIRECTION_INCREASE => 'Increase',
            self::DIRECTION_DECREASE => 'Decrease',
            self::DIRECTION_MAINTAIN => 'Maintain',
        ][$this->direction] ?? $this->direction;
    }

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
        ][$this->frequency] ?? $this->frequency;
    }

    /**
     * Get the current average achievement across all projects
     *
     * @return float
     */
    public function getCurrentAchievementAttribute(): float
    {
        return $this->projectIndicators()->avg('achievement_percentage') ?? 0;
    }

    /**
     * Get the progress percentage towards target
     *
     * @return float
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return 0;
        }

        $currentValue = $this->current_achievement;

        switch ($this->direction) {
            case self::DIRECTION_INCREASE:
                return min(($currentValue / $this->target_value) * 100, 100);
            
            case self::DIRECTION_DECREASE:
                if ($this->baseline_value == 0) return 0;
                $reduction = $this->baseline_value - $currentValue;
                $targetReduction = $this->baseline_value - $this->target_value;
                return min(($reduction / $targetReduction) * 100, 100);
            
            case self::DIRECTION_MAINTAIN:
                $deviation = abs($currentValue - $this->target_value);
                $allowedDeviation = $this->target_value * 0.1; // 10% tolerance
                return max(0, 100 - ($deviation / $allowedDeviation) * 100);
            
            default:
                return 0;
        }
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Check if indicator is on track
     *
     * @return bool
     */
    public function isOnTrack(): bool
    {
        return $this->progress_percentage >= 70;
    }

    /**
     * Get the status based on progress
     *
     * @return string
     */
    public function getStatus(): string
    {
        $progress = $this->progress_percentage;

        if ($progress >= 90) return 'excellent';
        if ($progress >= 70) return 'good';
        if ($progress >= 50) return 'needs_attention';
        return 'critical';
    }

    /**
     * Get formatted value with unit
     *
     * @param float|null $value
     * @return string
     */
    public function formatValue(?float $value): string
    {
        if ($value === null) return 'N/A';

        switch ($this->unit) {
            case self::UNIT_CURRENCY:
                return number_format($value, 2);
            
            case self::UNIT_PERCENTAGE:
                return number_format($value, 1) . '%';
            
            case self::UNIT_NUMBER:
            case self::UNIT_COUNT:
                return number_format($value, 0);
            
            default:
                return number_format($value, 2);
        }
    }

    /**
     * Get indicator statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'projects_count' => $this->projects()->count(),
            'active_projects_count' => $this->projects()->active()->count(),
            'data_entries_count' => $this->dataEntries()->count(),
            'current_achievement' => $this->current_achievement,
            'progress_percentage' => $this->progress_percentage,
            'is_on_track' => $this->isOnTrack(),
            'status' => $this->getStatus(),
        ];
    }

    /**
     * Get indicator summary for reporting
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
            'theme_name' => $this->theme?->name,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'unit' => $this->unit,
            'unit_label' => $this->unit_label,
            'baseline_value' => $this->formatValue($this->baseline_value),
            'target_value' => $this->formatValue($this->target_value),
            'direction' => $this->direction,
            'direction_label' => $this->direction_label,
            'frequency' => $this->frequency,
            'frequency_label' => $this->frequency_label,
            'is_active' => $this->is_active,
            'projects_count' => $stats['projects_count'],
            'current_achievement' => $this->formatValue($stats['current_achievement']),
            'progress_percentage' => number_format($stats['progress_percentage'], 1) . '%',
            'status' => $stats['status'],
        ];
    }

    /**
     * Check if indicator can be deleted (no associated projects)
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return $this->projects()->count() === 0;
    }
}
