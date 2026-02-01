<?php

namespace App\Models;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DataEntry Model
 * 
 * Manages data entries for indicators in the KMC M&E System
 * 
 * @property int $id
 * @property int $project_id
 * @property int $indicator_id
 * @property float $value
 * @property string|null $unit
 * @property \Carbon\Carbon $data_date
 * @property string $frequency
 * @property string|null $notes
 * @property string|null $data_source
 * @property string $verification_status
 * @property int|null $verified_by
 * @property \Carbon\Carbon|null $verified_at
 * @property string|null $verification_notes
 * @property int $entered_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class DataEntry extends Model
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
        'value',
        'unit',
        'data_date',
        'frequency',
        'notes',
        'data_source',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'entered_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'decimal:2',
        'data_date' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Frequency constants
     */
    const FREQUENCY_MONTHLY = 'MONTHLY';
    const FREQUENCY_QUARTERLY = 'QUARTERLY';
    const FREQUENCY_ANNUALLY = 'ANNUALLY';
    const FREQUENCY_ADHOC = 'ADHOC';

    /**
     * Verification status constants
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_VERIFIED = 'VERIFIED';
    const STATUS_REJECTED = 'REJECTED';

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

    /**
     * Get all possible verification statuses
     */
    public static function verificationStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_VERIFIED,
            self::STATUS_REJECTED,
        ];
    }

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Log data entry creation
        static::created(function ($dataEntry) {
            $request = app()->runningInConsole() ? null : request();
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'DATA_ENTERED',
                'entity_type' => 'DataEntry',
                'entity_id' => $dataEntry->id,
                'new_values' => $dataEntry->toArray(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);

            // Update project indicator current value
            $dataEntry->updateProjectIndicator();
        });

        // Log data entry updates
        static::updated(function ($dataEntry) {
            $request = app()->runningInConsole() ? null : request();
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'DATA_UPDATED',
                'entity_type' => 'DataEntry',
                'entity_id' => $dataEntry->id,
                'old_values' => $dataEntry->getOriginal(),
                'new_values' => $dataEntry->getChanges(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);

            // Update project indicator current value
            $dataEntry->updateProjectIndicator();
        });
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the project that owns the data entry
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the indicator that owns the data entry
     */
    public function indicator()
    {
        return $this->belongsTo(Indicator::class);
    }

    /**
     * Get the user who entered the data
     */
    public function enterer()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    /**
     * Alias for enterer (for consistency)
     */
    public function enteredBy()
    {
        return $this->enterer();
    }

    /**
     * Get the user who verified the data
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Alias for verifier (for consistency)
     */
    public function verifiedBy()
    {
        return $this->verifier();
    }

    /**
     * Get all file attachments for the data entry
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
     * Scope a query to filter by verification status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByVerificationStatus($query, string $status)
    {
        return $query->where('verification_status', $status);
    }

    /**
     * Scope a query to filter by date range
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('data_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to include pending verification
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendingVerification($query)
    {
        return $query->where('verification_status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to include verified entries
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', self::STATUS_VERIFIED);
    }

    /**
     * Scope a query to search data entries
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('notes', 'like', "%{$search}%")
              ->orWhere('data_source', 'like', "%{$search}%")
              ->orWhere('verification_notes', 'like', "%{$search}%");
        });
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
        ][$this->frequency] ?? $this->frequency;
    }

    /**
     * Get the verification status label
     *
     * @return string
     */
    public function getVerificationStatusLabelAttribute(): string
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_REJECTED => 'Rejected',
        ][$this->verification_status] ?? $this->verification_status;
    }

    /**
     * Get the verification status color
     *
     * @return string
     */
    public function getVerificationStatusColorAttribute(): string
    {
        return [
            self::STATUS_PENDING => 'warning',
            self::STATUS_VERIFIED => 'success',
            self::STATUS_REJECTED => 'danger',
        ][$this->verification_status] ?? 'secondary';
    }

    /**
     * Get formatted value with unit
     *
     * @return string
     */
    public function getFormattedValueAttribute(): string
    {
        $unit = $this->unit ?? $this->indicator?->unit_label ?? '';
        return number_format($this->value, 2) . ' ' . $unit;
    }

    /**
     * Check if data entry can be verified
     *
     * @return bool
     */
    public function getCanBeVerifiedAttribute(): bool
    {
        return $this->verification_status === self::STATUS_PENDING;
    }

    /**
     * Check if data entry can be edited
     *
     * @return bool
     */
    public function getCanBeEditedAttribute(): bool
    {
        return $this->verification_status === self::STATUS_PENDING;
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Verify the data entry
     *
     * @param int $verifiedBy
     * @param string|null $notes
     * @return bool
     */
    public function verify(int $verifiedBy, ?string $notes = null): bool
    {
        if (!$this->can_be_verified) {
            return false;
        }

        $this->verification_status = self::STATUS_VERIFIED;
        $this->verified_by = $verifiedBy;
        $this->verified_at = now();
        $this->verification_notes = $notes;

        return $this->save();
    }

    /**
     * Reject the data entry
     *
     * @param int $rejectedBy
     * @param string|null $notes
     * @return bool
     */
    public function reject(int $rejectedBy, ?string $notes = null): bool
    {
        if (!$this->can_be_verified) {
            return false;
        }

        $this->verification_status = self::STATUS_REJECTED;
        $this->verified_by = $rejectedBy;
        $this->verified_at = now();
        $this->verification_notes = $notes;

        return $this->save();
    }

    /**
     * Reset to pending status
     *
     * @return bool
     */
    public function resetToPending(): bool
    {
        $this->verification_status = self::STATUS_PENDING;
        $this->verified_by = null;
        $this->verified_at = null;
        $this->verification_notes = null;

        return $this->save();
    }

    /**
     * Update the project indicator with this data entry value
     *
     * @return void
     */
    public function updateProjectIndicator(): void
    {
        $projectIndicator = ProjectIndicator::where('project_id', $this->project_id)
                                          ->where('indicator_id', $this->indicator_id)
                                          ->first();

        if ($projectIndicator) {
            // Get the latest verified data entry for this indicator
            $latestEntry = static::byProject($this->project_id)
                                ->byIndicator($this->indicator_id)
                                ->verified()
                                ->latest('data_date')
                                ->first();

            if ($latestEntry) {
                $projectIndicator->updateValue(
                    $latestEntry->value,
                    auth()->id(),
                    'Updated from latest data entry'
                );
            }
        }
    }

    /**
     * Get data entry summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'project_name' => $this->project?->name,
            'indicator_name' => $this->indicator?->name,
            'indicator_code' => $this->indicator?->code,
            'value' => $this->formatted_value,
            'unit' => $this->unit ?? $this->indicator?->unit_label,
            'data_date' => $this->data_date->format('Y-m-d'),
            'frequency' => $this->frequency,
            'frequency_label' => $this->frequency_label,
            'notes' => $this->notes,
            'data_source' => $this->data_source,
            'verification_status' => $this->verification_status,
            'verification_status_label' => $this->verification_status_label,
            'verification_status_color' => $this->verification_status_color,
            'verified_by' => $this->verifier?->full_name,
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
            'verification_notes' => $this->verification_notes,
            'entered_by' => $this->enterer?->full_name,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check if user can perform action on this data entry
     *
     * @param User $user
     * @param string $action
     * @return bool
     */
    public function canUserPerformAction(User $user, string $action): bool
    {
        switch ($action) {
            case 'view':
                return $user->hasPermission('view_data');
            
            case 'edit':
                return $user->hasPermission('edit_data') && 
                       $this->can_be_edited &&
                       ($this->entered_by === $user->id || $user->hasPermission('edit_all_data'));
            
            case 'verify':
                return $user->hasPermission('verify_data') && $this->can_be_verified;
            
            case 'delete':
                return $user->hasPermission('delete_data') && 
                       $this->can_be_edited &&
                       ($this->entered_by === $user->id || $user->hasPermission('delete_all_data'));
            
            default:
                return false;
        }
    }

    /**
     * Get data entry statistics for a project
     *
     * @param int $projectId
     * @return array
     */
    public static function getProjectStats(int $projectId): array
    {
        $entries = static::byProject($projectId);
        
        return [
            'total' => $entries->count(),
            'pending' => $entries->pendingVerification()->count(),
            'verified' => $entries->verified()->count(),
            'rejected' => $entries->byVerificationStatus(self::STATUS_REJECTED)->count(),
        ];
    }

    /**
     * Get data entry statistics for an indicator
     *
     * @param int $indicatorId
     * @return array
     */
    public static function getIndicatorStats(int $indicatorId): array
    {
        $entries = static::byIndicator($indicatorId);
        
        return [
            'total' => $entries->count(),
            'pending' => $entries->pendingVerification()->count(),
            'verified' => $entries->verified()->count(),
            'rejected' => $entries->byVerificationStatus(self::STATUS_REJECTED)->count(),
            'latest_value' => $entries->verified()->latest('data_date')->first()?->value,
        ];
    }

    /**
     * Get trend data for an indicator
     *
     * @param int $projectId
     * @param int $indicatorId
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTrendData(
        int $projectId, 
        int $indicatorId, 
        string $startDate, 
        string $endDate
    ) {
        return static::byProject($projectId)
                    ->byIndicator($indicatorId)
                    ->verified()
                    ->byDateRange($startDate, $endDate)
                    ->orderBy('data_date')
                    ->get(['data_date', 'value']);
    }
}
