<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    protected $fillable = [
        'event_id', 'name', 'description', 'price',
        'quantity', 'available_quantity', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function isAvailable(int $quantity = 1): bool
    {
        return $this->status === 'active' && $this->available_quantity >= $quantity;
    }

    public function decrementAvailability(int $quantity): void
    {
        $this->decrement('available_quantity', $quantity);
    }

    public function incrementAvailability(int $quantity): void
    {
        $this->increment('available_quantity', $quantity);
    }
}
