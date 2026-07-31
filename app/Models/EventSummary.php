<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSummary extends Model
{
    use DeletesMediaFiles;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'event_id',
        'summary',
        'status',
        'user_id',
        'published_at',
        'smm_title',
        'smm_description',
        'smm_image',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EventSummaryImage::class)
            ->orderBy('sort_order');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EventSummaryHistory::class)
            ->latest();
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    protected function mediaFields(): array
    {
        return ['smm_image'];
    }
}
