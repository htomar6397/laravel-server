<?php

namespace App\Models;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * PhotoCapture Model
 * 
 * Manages photo captures for projects in the KMC M&E System
 * 
 * @property int $id
 * @property string $code
 * @property int|null $project_id
 * @property string $title
 * @property string|null $description
 * @property string $file_path
 * @property string|null $thumbnail_path
 * @property string $original_filename
 * @property string $mime_type
 * @property int $file_size
 * @property int|null $width
 * @property int|null $height
 * @property float|null $latitude
 * @property float|null $longitude
 * @property float|null $accuracy
 * @property \Carbon\Carbon|null $captured_at
 * @property string $status
 * @property int $captured_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class PhotoCapture extends Model
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
        'title',
        'description',
        'file_path',
        'thumbnail_path',
        'original_filename',
        'mime_type',
        'file_size',
        'width',
        'height',
        'latitude',
        'longitude',
        'accuracy',
        'captured_at',
        'status',
        'captured_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'captured_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_DELETED = 'DELETED';

    /**
     * Get all possible statuses
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_DELETED,
        ];
    }

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Generate unique code before creating
        static::creating(function ($photo) {
            if (empty($photo->code)) {
                $photo->code = 'PHOTO-' . date('Y') . '-' . Str::uuid()->toString();
            }
        });

        // Set captured_at if not provided
        static::creating(function ($photo) {
            if (empty($photo->captured_at)) {
                $photo->captured_at = now();
            }
        });

        // Log photo creation
        static::created(function ($photo) {
            $request = app()->runningInConsole() ? null : request();
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'PHOTO_CAPTURED',
                'entity_type' => 'PhotoCapture',
                'entity_id' => $photo->id,
                'new_values' => $photo->toArray(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        });

        // Delete files when photo is permanently deleted
        static::deleted(function ($photo) {
            if ($photo->isForceDeleting()) {
                Storage::disk('public')->delete($photo->file_path);
                if ($photo->thumbnail_path) {
                    Storage::disk('public')->delete($photo->thumbnail_path);
                }
            }
        });
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the project that owns the photo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who captured the photo
     */
    public function capturer()
    {
        return $this->belongsTo(User::class, 'captured_by');
    }

    /**
     * Alias for capturer (for consistency)
     */
    public function capturedBy()
    {
        return $this->capturer();
    }

    /**
     * Get all file attachments for the photo
     */
    public function fileAttachments()
    {
        return $this->morphMany(FileAttachment::class, 'entity');
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

    /**
     * Scope a query to only include active photos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

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
     * Scope a query to filter by user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('captured_by', $userId);
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
        return $query->whereBetween('captured_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to include photos with GPS coordinates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithGPS($query)
    {
        return $query->whereNotNull('latitude')
                    ->whereNotNull('longitude');
    }

    /**
     * Scope a query to search photos
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
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('original_filename', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to include recent photos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('captured_at', '>=', now()->subDays($days));
    }

    // ========================================================================
    // CALCULATED ATTRIBUTES
    // ========================================================================

    /**
     * Get the status label
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_DELETED => 'Deleted',
        ][$this->status] ?? $this->status;
    }

    /**
     * Get the file size in human readable format
     *
     * @return string
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the image URL
     *
     * @return string
     */
    public function getImageUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the thumbnail URL
     *
     * @return string|null
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }

    /**
     * Check if photo has GPS coordinates
     *
     * @return bool
     */
    public function hasGPSCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Get GPS accuracy status
     *
     * @return string
     */
    public function getGpsAccuracyStatusAttribute(): string
    {
        if (!$this->accuracy) return 'Unknown';
        
        if ($this->accuracy <= 5) return 'Excellent';
        if ($this->accuracy <= 10) return 'Good';
        if ($this->accuracy <= 20) return 'Fair';
        return 'Poor';
    }

    /**
     * Get GPS coordinates as formatted string
     *
     * @return string|null
     */
    public function getGpsCoordinatesAttribute(): ?string
    {
        if (!$this->hasGPSCoordinates()) return null;
        
        return number_format($this->latitude, 6) . ', ' . number_format($this->longitude, 6);
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Generate thumbnail for the image
     *
     * @param int $width
     * @param int $height
     * @return string|null
     */
    public function generateThumbnail(int $width = 300, int $height = 200): ?string
    {
        if (!str_starts_with($this->mime_type, 'image/')) {
            return null;
        }

        try {
            $image = \Intervention\Image\Facades\Image::make(Storage::disk('public')->path($this->file_path));
            $image->fit($width, $height);
            
            $thumbnailPath = 'thumbnails/' . uniqid() . '_' . $this->original_filename;
            Storage::disk('public')->put($thumbnailPath, $image->encode());
            
            $this->thumbnail_path = $thumbnailPath;
            $this->save();
            
            return $thumbnailPath;
        } catch (\Exception $e) {
            \Log::error('Failed to generate thumbnail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Soft delete the photo (mark as deleted)
     *
     * @return bool
     */
    public function softDeletePhoto(): bool
    {
        $this->status = self::STATUS_DELETED;
        return $this->save();
    }

    /**
     * Restore the photo (mark as active)
     *
     * @return bool
     */
    public function restorePhoto(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Get EXIF data from the image
     *
     * @return array
     */
    public function getExifData(): array
    {
        if (!str_starts_with($this->mime_type, 'image/')) {
            return [];
        }

        try {
            $image = \Intervention\Image\Facades\Image::make(Storage::disk('public')->path($this->file_path));
            $exif = $image->exif();
            
            return [
                'camera_make' => $exif['Make'] ?? null,
                'camera_model' => $exif['Model'] ?? null,
                'datetime' => $exif['DateTime'] ?? null,
                'gps_data' => $this->hasGPSCoordinates() ? [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'accuracy' => $this->accuracy,
                ] : null,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get photo summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'project_name' => $this->project?->name,
            'title' => $this->title,
            'description' => $this->description,
            'original_filename' => $this->original_filename,
            'file_size' => $this->file_size_human,
            'dimensions' => $this->width && $this->height ? "{$this->width}x{$this->height}" : 'N/A',
            'mime_type' => $this->mime_type,
            'image_url' => $this->image_url,
            'thumbnail_url' => $this->thumbnail_url,
            'has_gps' => $this->hasGPSCoordinates(),
            'gps_coordinates' => $this->gps_coordinates,
            'gps_accuracy' => $this->accuracy,
            'gps_accuracy_status' => $this->gps_accuracy_status,
            'captured_at' => $this->captured_at?->format('Y-m-d H:i:s'),
            'captured_by' => $this->capturer?->full_name,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check if user can perform action on this photo
     *
     * @param User $user
     * @param string $action
     * @return bool
     */
    public function canUserPerformAction(User $user, string $action): bool
    {
        switch ($action) {
            case 'view':
                return $user->hasPermission('view_photos');
            
            case 'edit':
                return $user->hasPermission('edit_photos') && $this->captured_by === $user->id;
            
            case 'delete':
                return $user->hasPermission('delete_photos') && $this->captured_by === $user->id;
            
            default:
                return false;
        }
    }

    /**
     * Get photo metadata for API responses
     *
     * @return array
     */
    public function getApiMetadata(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'thumbnail_url' => $this->thumbnail_url,
            'dimensions' => [
                'width' => $this->width,
                'height' => $this->height,
            ],
            'file_size' => $this->file_size,
            'gps' => $this->hasGPSCoordinates() ? [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'accuracy' => $this->accuracy,
                'coordinates' => $this->gps_coordinates,
            ] : null,
            'captured_at' => $this->captured_at?->toISOString(),
            'project' => $this->project ? [
                'id' => $this->project->id,
                'name' => $this->project->name,
                'code' => $this->project->code,
            ] : null,
            'capturer' => [
                'id' => $this->capturer->id,
                'name' => $this->capturer->full_name,
            ],
        ];
    }
}
