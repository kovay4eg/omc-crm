<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MediaArchive extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_UPLOADING = 'uploading';

    public const STATUS_UPLOADED = 'uploaded';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_FAILED = 'failed';

    /** Alias used by jobs that describe the final state explicitly. */
    public const STATUS_LOCAL_DELETED = self::STATUS_ARCHIVED;

    protected $fillable = [
        'original_path',
        'source_disk',
        'remote_path',
        'remote_file_id',
        'remote_revision_id',
        'sha256',
        'size',
        'mime_type',
        'reason',
        'source_type',
        'source_id',
        'status',
        'scan_status',
        'attempts',
        'last_error',
        'eligible_at',
        'uploaded_at',
        'verified_at',
        'archived_at',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'attempts' => 'integer',
            'eligible_at' => 'datetime',
            'uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
            'archived_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function scopeRemotelyAvailable(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_VERIFIED, self::STATUS_ARCHIVED]);
    }
}
