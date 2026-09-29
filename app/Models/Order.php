<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'event_id', 'reference', 'customer_name', 'customer_phone',
        'customer_email', 'customer_note', 'total_amount', 'status', 'expires_at',
        'whatsapp_clicked_at', 'paid_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'whatsapp_clicked_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->reference ??= 'ORD-' . strtoupper(Str::random(10));
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(OrderNotification::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'pending' && $this->expires_at?->isPast();
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** En attente de traitement par l'admin (et pas encore expirée). */
    public function isAwaitingConfirmation(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    /** Commandes à traiter : en attente et dont les places sont encore réservées. */
    public function scopeAwaiting($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }
}