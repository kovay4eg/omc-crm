<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSummaryImage extends Model
{
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
}