<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Project Model
 * 
 * Manages development projects within Kibaha Municipal Council
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int|null $theme_id
 * @property int|null $org_unit_id
 * @property string|null $sector
 * @property string|null $donor
 * @property float|null $budget
 * @property string $currency
 * @property date|null $start_date
 * @property date|null $end_date
 * @property string $status
 * @property float $completion_percentage
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int|null $created_by
 */
class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'theme_id',
        'org_unit_id',
        'sector',
        'donor',
        'budget',
        'currency',
        'start_date',
        'end_date',
        'status',
        'completion_percentage',
        'latitude',
        'longitude',
        'geojson',
        'location_description',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Project status constants
     */
    const STATUS_PLANNING = 'PLANNING';
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_SUSPENDED = 'SUSPENDED';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELLED = 'CANCELLED';

    /**
     * Get all possible project statuses
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PLANNING,
            self::STATUS_ACTIVE,
            self::STATUS_SUSPENDED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the theme that owns the project
     */
    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Get the organizational unit that owns the project
     */
    public function organizationalUnit()
    {
        return $this->belongsTo(OrganizationalUnit::class, 'org_unit_id');
    }

    /**
     * Get the user who created the project
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the project indicators for the project
     */
    public function projectIndicators()
    {
        return $this->hasMany(ProjectIndicator::class);
    }

    /**
     * Get the indicators through project_indicators pivot
     */
    public function indicators()
    {
        return $this->belongsToMany(Indicator::class, 'project_indicators')
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
     * Get the results for the project
     */
    public function results()
    {
        return $this->hasMany(ProjectResult::class);
    }

    /**
     * Get the data entries for the project
     */
    public function dataEntries()
    {
        return $this->hasMany(DataEntry::class);
    }

    /**
     * Get the expenditures for the project
     */
    public function expenditures()
    {
        return $this->hasMany(Expenditure::class);
    }

    /**
     * Get the photo captures for the project
     */
    public function photoCaptures()
    {
        return $this->hasMany(PhotoCapture::class);
    }

    /**
     * Get all file attachments for the project
     */
    public function fileAttachments()
    {
        return $this->morphMany(FileAttachment::class, 'entity');
    }

    // ========================================================================
    // CALCULATED ATTRIBUTES
    // ========================================================================

    /**
     * Get the total expenditures for the project
     */
    public function getTotalExpendituresAttribute(): float
    {
        return $this->expenditures()->sum('amount');
    }

    /**
     * Get the budget utilization percentage
     */
    public function getBudgetUtilizationAttribute(): float
    {
        if (!$this->budget || $this->budget == 0) {
            return 0;
        }
        return ($this->total_expenditures / $this->budget) * 100;
    }

    /**
     * Get the average achievement percentage across all indicators
     */
    public function getAverageAchievementAttribute(): float
    {
        return $this->projectIndicators()
            ->avg('achievement_percentage') ?? 0;
    }

    /**
     * Check if project is on track (>= 70% of expected progress)
     */
    public function getIsOnTrackAttribute(): bool
    {
        if (!$this->start_date || !$this->end_date) {
            return true;
        }

        $totalDays = $this->start_date->diffInDays($this->end_date);
        $elapsedDays = $this->start_date->diffInDays(now());
        
        if ($totalDays == 0) {
            return true;
        }

        $expectedProgress = ($elapsedDays / $totalDays) * 100;
        $expectedProgress = min(max($expectedProgress, 0), 100);

        return $this->completion_percentage >= ($expectedProgress * 0.7);
    }

    /**
     * Get project health status
     */
    public function getHealthStatusAttribute(): string
    {
        if ($this->status === self::STATUS_COMPLETED) {
            return 'completed';
        }

        if ($this->status === self::STATUS_CANCELLED) {
            return 'cancelled';
        }

        if (!$this->is_on_track) {
            return 'at_risk';
        }

        if ($this->average_achievement >= 80) {
            return 'excellent';
        }

        if ($this->average_achievement >= 60) {
            return 'good';
        }

        return 'needs_attention';
    }

    /**
     * Get days remaining until project end
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return max(0, now()->diffInDays($this->end_date, false));
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to only include active projects
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include completed projects
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to filter by theme
     */
    public function scopeByTheme($query, int $themeId)
    {
        return $query->where('theme_id', $themeId);
    }

    /**
     * Scope a query to filter by organizational unit
     */
    public function scopeByOrgUnit($query, int $orgUnitId)
    {
        return $query->where('org_unit_id', $orgUnitId);
    }

    /**
     * Scope a query to search projects
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
     * Scope a query to include projects ending soon (within specified days)
     */
    public function scopeEndingSoon($query, int $days = 30)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->whereNotNull('end_date')
                    ->whereRaw('DATEDIFF(end_date, CURDATE()) <= ?', [$days])
                    ->whereRaw('DATEDIFF(end_date, CURDATE()) >= 0');
    }

    /**
     * Scope a query to include at-risk projects
     */
    public function scopeAtRisk($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('completion_percentage', '<', 50);
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Update project completion percentage based on indicators
     */
    public function updateCompletionPercentage(): void
    {
        $avgAchievement = $this->projectIndicators()
            ->avg('achievement_percentage');

        if ($avgAchievement !== null) {
            $this->completion_percentage = $avgAchievement;
            $this->save();
        }
    }

    /**
     * Mark project as completed
     */
    public function markAsCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completion_percentage = 100;
        return $this->save();
    }

    /**
     * Check if project has GPS coordinates
     */
    public function hasGPSCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Get project summary for reporting
     */
    public function getSummary(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'theme' => $this->theme?->name,
            'status' => $this->status,
            'budget' => number_format($this->budget, 2),
            'expenditure' => number_format($this->total_expenditures, 2),
            'budget_utilization' => number_format($this->budget_utilization, 1) . '%',
            'completion' => number_format($this->completion_percentage, 1) . '%',
            'health_status' => $this->health_status,
            'days_remaining' => $this->days_remaining,
        ];
    }
}
