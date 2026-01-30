<?php

namespace App\Models;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * FileAttachment Model
 * 
 * Manages file attachments for various entities in the KMC M&E System
 * 
 * @property int $id
 * @property string $filename
 * @property string $original_filename
 * @property string $mime_type
 * @property int $file_size
 * @property string $file_path
 * @property string|null $thumbnail_path
 * @property string|null $description
 * @property string $entity_type
 * @property int $entity_id
 * @property int $uploaded_by
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class FileAttachment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filename',
        'original_filename',
        'mime_type',
        'file_size',
        'file_path',
        'thumbnail_path',
        'description',
        'entity_type',
        'entity_id',
        'uploaded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Boot method to register model events
     */
    protected static function boot()
    {
        parent::boot();

        // Log file upload
        static::created(function ($attachment) {
            $request = app()->runningInConsole() ? null : request();
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'FILE_UPLOADED',
                'entity_type' => 'FileAttachment',
                'entity_id' => $attachment->id,
                'new_values' => $attachment->toArray(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        });

        // Delete files when attachment is permanently deleted
        static::deleted(function ($attachment) {
            if ($attachment->isForceDeleting()) {
                Storage::disk('public')->delete($attachment->file_path);
                if ($attachment->thumbnail_path) {
                    Storage::disk('public')->delete($attachment->thumbnail_path);
                }
            }
        });
    }

    // ========================================================================
    // RELATIONSHIPS
    // ========================================================================

    /**
     * Get the user who uploaded the file
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the owning entity (polymorphic relationship)
     */
    public function entity()
    {
        return $this->morphTo();
    }

    // ========================================================================
    // QUERY SCOPES
    // ========================================================================

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
     * Scope a query to filter by entity
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $entityType
     * @param int $entityId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
                    ->where('entity_id', $entityId);
    }

    /**
     * Scope a query to filter by mime type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $mimeType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMimeType($query, string $mimeType)
    {
        return $query->where('mime_type', $mimeType);
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
        return $query->where('uploaded_by', $userId);
    }

    /**
     * Scope a query to search attachments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('original_filename', 'like', "%{$search}%")
              ->orWhere('filename', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to include images only
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope a query to include documents only
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDocuments($query)
    {
        return $query->where('mime_type', 'like', 'application/%')
                    ->orWhere('mime_type', 'like', 'text/%');
    }

    // ========================================================================
    // CALCULATED ATTRIBUTES
    // ========================================================================

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
     * Get the file URL
     *
     * @return string
     */
    public function getFileUrlAttribute(): string
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
     * Check if file is an image
     *
     * @return bool
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a document
     *
     * @return bool
     */
    public function getIsDocumentAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'application/') || 
               str_starts_with($this->mime_type, 'text/');
    }

    /**
     * Get the file extension
     *
     * @return string
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type category
     *
     * @return string
     */
    public function getFileTypeAttribute(): string
    {
        if ($this->is_image) {
            return 'Image';
        } elseif ($this->is_document) {
            return 'Document';
        } elseif (str_starts_with($this->mime_type, 'video/')) {
            return 'Video';
        } elseif (str_starts_with($this->mime_type, 'audio/')) {
            return 'Audio';
        } else {
            return 'Other';
        }
    }

    /**
     * Get the file icon based on mime type
     *
     * @return string
     */
    public function getFileIconAttribute(): string
    {
        if (str_starts_with($this->mime_type, 'image/')) {
            return 'fas fa-image';
        } elseif (str_starts_with($this->mime_type, 'application/pdf')) {
            return 'fas fa-file-pdf';
        } elseif (str_starts_with($this->mime_type, 'application/msword') || 
                  str_starts_with($this->mime_type, 'application/vnd.openxmlformats-officedocument.wordprocessingml')) {
            return 'fas fa-file-word';
        } elseif (str_starts_with($this->mime_type, 'application/vnd.ms-excel') || 
                  str_starts_with($this->mime_type, 'application/vnd.openxmlformats-officedocument.spreadsheetml')) {
            return 'fas fa-file-excel';
        } elseif (str_starts_with($this->mime_type, 'application/vnd.ms-powerpoint') || 
                  str_starts_with($this->mime_type, 'application/vnd.openxmlformats-officedocument.presentationml')) {
            return 'fas fa-file-powerpoint';
        } elseif (str_starts_with($this->mime_type, 'text/')) {
            return 'fas fa-file-alt';
        } elseif (str_starts_with($this->mime_type, 'video/')) {
            return 'fas fa-video';
        } elseif (str_starts_with($this->mime_type, 'audio/')) {
            return 'fas fa-audio';
        } elseif (str_starts_with($this->mime_type, 'application/zip') || 
                  str_starts_with($this->mime_type, 'application/x-rar-compressed')) {
            return 'fas fa-file-archive';
        } else {
            return 'fas fa-file';
        }
    }

    // ========================================================================
    // UTILITY METHODS
    // ========================================================================

    /**
     * Generate thumbnail for image files
     *
     * @param int $width
     * @param int $height
     * @return string|null
     */
    public function generateThumbnail(int $width = 300, int $height = 200): ?string
    {
        if (!$this->is_image) {
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
     * Check if user can download this file
     *
     * @param User $user
     * @return bool
     */
    public function canUserDownload(User $user): bool
    {
        // Check if user has permission to view the entity
        switch ($this->entity_type) {
            case 'Project':
                return $user->hasPermission('view_projects');
            case 'Expenditure':
                return $user->hasPermission('view_expenditures');
            case 'PhotoCapture':
                return $user->hasPermission('view_photos');
            default:
                return $user->hasPermission('view_files');
        }
    }

    /**
     * Check if user can delete this file
     *
     * @param User $user
     * @return bool
     */
    public function canUserDelete(User $user): bool
    {
        // User can delete if they uploaded it or have admin permissions
        if ($this->uploaded_by === $user->id) {
            return true;
        }

        return $user->hasPermission('delete_files');
    }

    /**
     * Get file attachment summary for reporting
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'file_size' => $this->file_size_human,
            'file_type' => $this->file_type,
            'file_extension' => $this->file_extension,
            'mime_type' => $this->mime_type,
            'file_icon' => $this->file_icon,
            'is_image' => $this->is_image,
            'is_document' => $this->is_document,
            'file_url' => $this->file_url,
            'thumbnail_url' => $this->thumbnail_url,
            'description' => $this->description,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'uploaded_by' => $this->uploader?->full_name,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Upload a file and create attachment record
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $entityType
     * @param int $entityId
     * @param string|null $description
     * @param int|null $uploadedBy
     * @return static
     */
    public static function uploadFile(
        \Illuminate\Http\UploadedFile $file,
        string $entityType,
        int $entityId,
        ?string $description = null,
        ?int $uploadedBy = null
    ): static {
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('attachments', $filename, 'public');

        return static::create([
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_path' => $path,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'uploaded_by' => $uploadedBy ?? auth()->id(),
        ]);
    }

    /**
     * Get storage usage statistics
     *
     * @return array
     */
    public static function getStorageStats(): array
    {
        $totalSize = static::sum('file_size');
        $totalFiles = static::count();
        $imageFiles = static::images()->count();
        $documentFiles = static::documents()->count();

        return [
            'total_size' => $totalSize,
            'total_size_human' => self::formatBytes($totalSize),
            'total_files' => $totalFiles,
            'image_files' => $imageFiles,
            'document_files' => $documentFiles,
            'other_files' => $totalFiles - $imageFiles - $documentFiles,
        ];
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
