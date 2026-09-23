<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $fillable = [
        'order_id', 'ticket_type_id', 'ticket_number', 'qr_code',
        'status', 'generated_at', 'used_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->qr_code ??= (string) Str::uuid();
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(TicketScan::class);
    }

    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    public function markAsUsed(): void
    {
        $this->update(['status' => 'used', 'used_at' => now()]);
    }
}
