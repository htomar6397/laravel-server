<?php

namespace App\Models;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Expenditure Model
 * 
 * Manages financial expenditures for projects in the KMC M&E System
 * 
 * @property int $id
 * @property string $code
 * @property int $project_id
 * @property string $description
 * @property float $amount
 * @property string $currency
 * @property \Carbon\Carbon $expenditure_date
 * @property string $category
 * @property string|null $subcategory
 * @property string|null $vendor
 * @property string|null $invoice_number
 * @property string|null $receipt_number
 * @property string $status
 * @property int|null $approved_by
 * @property \Carbon\Carbon|null $approved_at
 * @property string|null $approval_notes
 * @property int|null $verified_by
 * @property \Carbon\Carbon|null $verified_at
 * @property string|null $verification_notes
 * @property int $entered_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Expenditure extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'project_id',
        'description',
        'amount',
        'currency',
        'expenditure_date',
        'category',
        'subcategory',
        'vendor',
        'invoice_number',
        'receipt_number',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
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
        'amount' => 'decimal:2',
        'expenditure_date' => 'date',
        'approved_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Category constants
     */
    const CATEGORY_PERSONNEL = 'PERSONNEL';
    const CATEGORY_EQUIPMENT = 'EQUIPMENT';
    const CATEGORY_MATERIALS = 'MATERIALS';
    const CATEGORY_SERVICES = 'SERVICES';
    const CATEGORY_TRAVEL = 'TRAVEL';
    const CATEGORY_OVERHEAD = 'OVERHEAD';
    const CATEGORY_OTHER = 'OTHER';

    /**
     * Status constants
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_VERIFIED = 'VERIFIED';

    /**
     * Get all possible categories
     */
    public static function categories(): array
    {
        return [
            self::CATEGORY_PERSONNEL,
            self::CATEGORY_EQUIPMENT,
            self::CATEGORY_MATERIALS,
            self::CATEGORY_SERVICES,
            self::CATEGORY_TRAVEL,
            self::CATEGORY_OVERHEAD,
            self::CATEGORY_OTHER,
        ];
    }

    /**
     * Get all possible statuses
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_VERIFIED,
        ];
    }

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Generate unique code before creating
        static::creating(function ($expenditure) {
            if (empty($expenditure->code)) {
                $expenditure->code = 'EXP-' . date('Y') . '-' . Str::uuid()->toString();
            }
        });

        // Log expenditure creation
        static::created(function ($expenditure) {
            $request = app()->runningInConsole() ? null : request();
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'EXPENDITURE_CREATED',
                'entity_type' => 'Expenditure',
                'entity_id' => $expenditure->id,
                'new_values' => $expenditure->toArray(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        });

        // Log expenditure updates
        static::updated(function ($expenditure) {
            $request = app()->runningInConsole() ? null : request();
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'EXPENDITURE_UPDATED',
                'entity_type' => 'Expenditure',
                'entity_id' => $expenditure->id,
                'old_values' => $expenditure->getOriginal(),
                'new_values' => $expenditure->getChanges(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        });
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the project that owns the expenditure
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who approved the expenditure
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who verified the expenditure
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the user who entered the expenditure
     */
    public function enterer()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    /**
     * Get all file attachments for the expenditure
     */
    public function fileAttachments()
    {
        return $this->morphMany(FileAttachment::class, 'entity');
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

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
     * Scope a query to filter by date range
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('expenditure_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by amount range
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $minAmount
     * @param float $maxAmount
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAmountRange($query, float $minAmount, float $maxAmount)
    {
        return $query->whereBetween('amount', [$minAmount, $maxAmount]);
    }

    /**
     * Scope a query to search expenditures
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('description', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('vendor', 'like', "%{$search}%")
              ->orWhere('invoice_number', 'like', "%{$search}%")
              ->orWhere('receipt_number', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to include pending approval
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to include approved expenditures
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to include verified expenditures
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    // ========================================================================
    // CALCULATED ATTRIBUTES
    // ========================================================================

    /**
     * Get the category label
     *
     * @return string
     */
    public function getCategoryLabelAttribute(): string
    {
        return [
            self::CATEGORY_PERSONNEL => 'Personnel',
            self::CATEGORY_EQUIPMENT => 'Equipment',
            self::CATEGORY_MATERIALS => 'Materials',
            self::CATEGORY_SERVICES => 'Services',
            self::CATEGORY_TRAVEL => 'Travel',
            self::CATEGORY_OVERHEAD => 'Overhead',
            self::CATEGORY_OTHER => 'Other',
        ][$this->category] ?? $this->category;
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
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_VERIFIED => 'Verified',
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
            self::STATUS_APPROVED => 'info',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_VERIFIED => 'success',
        ][$this->status] ?? 'secondary';
    }

    /**
     * Check if expenditure can be approved
     *
     * @return bool
     */
    public function getCanBeApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if expenditure can be verified
     *
     * @return bool
     */
    public function getCanBeVerifiedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if expenditure can be edited
     *
     * @return bool
     */
    public function getCanBeEditedAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Approve the expenditure
     *
     * @param int $approvedBy
     * @param string|null $notes
     * @return bool
     */
    public function approve(int $approvedBy, ?string $notes = null): bool
    {
        if (!$this->can_be_approved) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approvedBy;
        $this->approved_at = now();
        $this->approval_notes = $notes;

        return $this->save();
    }

    /**
     * Reject the expenditure
     *
     * @param int $rejectedBy
     * @param string|null $notes
     * @return bool
     */
    public function reject(int $rejectedBy, ?string $notes = null): bool
    {
        if (!$this->can_be_approved) {
            return false;
        }

        $this->status = self::STATUS_REJECTED;
        $this->approved_by = $rejectedBy;
        $this->approved_at = now();
        $this->approval_notes = $notes;

        return $this->save();
    }

    /**
     * Verify the expenditure
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

        $this->status = self::STATUS_VERIFIED;
        $this->verified_by = $verifiedBy;
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
        $this->status = self::STATUS_PENDING;
        $this->approved_by = null;
        $this->approved_at = null;
        $this->approval_notes = null;
        $this->verified_by = null;
        $this->verified_at = null;
        $this->verification_notes = null;

        return $this->save();
    }

    /**
     * Get formatted amount with currency
     *
     * @return string
     */
    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get expenditure summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'project_name' => $this->project?->name,
            'description' => $this->description,
            'amount' => $this->getFormattedAmount(),
            'expenditure_date' => $this->expenditure_date->format('Y-m-d'),
            'category' => $this->category,
            'category_label' => $this->category_label,
            'subcategory' => $this->subcategory,
            'vendor' => $this->vendor,
            'invoice_number' => $this->invoice_number,
            'receipt_number' => $this->receipt_number,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'entered_by' => $this->enterer?->full_name,
            'approved_by' => $this->approver?->full_name,
            'verified_by' => $this->verifier?->full_name,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check if user can perform action on this expenditure
     *
     * @param User $user
     * @param string $action
     * @return bool
     */
    public function canUserPerformAction(User $user, string $action): bool
    {
        switch ($action) {
            case 'approve':
                return $user->canApproveExpenditures() && $this->can_be_approved;
            
            case 'verify':
                return $user->canApproveExpenditures() && $this->can_be_verified;
            
            case 'edit':
                return $user->hasPermission('edit_expenditures') && $this->can_be_edited;
            
            case 'delete':
                return $user->hasPermission('delete_expenditures') && $this->can_be_edited;
            
            default:
                return false;
        }
    }
}
