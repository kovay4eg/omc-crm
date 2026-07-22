<?php

namespace App\Models;

use App\Models\Concerns\DeletesMediaFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSummaryImage extends Model
{
    use DeletesMediaFiles;

    protected $fillable = [
        'event_summary_id',
        'image',
        'alt_text',
        'sort_order',
    ];

    public function eventSummary(): BelongsTo
    {
        return $this->belongsTo(EventSummary::class);
    }

    protected function mediaFields(): array
    {
        return ['image'];
    }
}
