<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPoster extends Model
{
    protected $fillable = ['event_id', 'mime', 'data'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function binary(): string
    {
        return (string) base64_decode($this->data, true);
    }
}
