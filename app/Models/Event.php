<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'image',
        'date', 'start_time', 'end_time', 'venue', 'address', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function poster(): HasOne
    {
        return $this->hasOne(EventPoster::class);
    }

    /** Version légère (sans les données de l'image) pour connaître l'existence et la date de l'affiche. */
    public function posterMeta(): HasOne
    {
        return $this->hasOne(EventPoster::class)->select(['id', 'event_id', 'updated_at']);
    }

    /** URL de l'affiche (stockée en base), ou ancienne affiche sur disque, ou null. */
    public function posterUrl(): ?string
    {
        if ($meta = $this->posterMeta) {
            return route('events.poster', ['event' => $this->id, 'v' => $meta->updated_at?->timestamp]);
        }

        return $this->image ? Storage::url($this->image) : null;
    }

    public function hasPoster(): bool
    {
        return $this->posterUrl() !== null;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /* -----------------------------------------------------------------
     |  Aides d'affichage (site public)
     | ----------------------------------------------------------------- */

    /** « samedi 17 octobre 2026 » */
    public function dateLong(): string
    {
        return $this->date->copy()->locale('fr')->translatedFormat('l j F Y');
    }

    /** « 19h00 » */
    public function startTimeLabel(): string
    {
        return self::timeLabel($this->start_time);
    }

    /** « 22h00 » ou null si aucune heure de fin. */
    public function endTimeLabel(): ?string
    {
        return $this->end_time ? self::timeLabel($this->end_time) : null;
    }

    private static function timeLabel(?string $time): string
    {
        return str_replace(':', 'h', substr((string) $time, 0, 5));
    }

    /** Nombre de jours avant le spectacle (0 = aujourd'hui, négatif = passé). */
    public function daysUntil(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->date->copy()->startOfDay(), false);
    }

    public function isOver(): bool
    {
        return $this->daysUntil() < 0 || in_array($this->status, ['completed', 'cancelled'], true);
    }

    public function countdownLabel(): ?string
    {
        $days = $this->daysUntil();

        return match (true) {
            $days < 0 => null,
            $days === 0 => "C'est aujourd'hui",
            $days === 1 => "C'est demain",
            default => "Dans {$days} jours",
        };
    }

    public function mapUrl(): string
    {
        $query = trim(($this->venue ?? '') . ' ' . ($this->address ?? ''));

        return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($query);
    }

    /** Catégories réellement en vente, du moins cher au plus cher. */
    public function sellableTicketTypes()
    {
        return $this->ticketTypes()->where('status', 'active')->orderBy('price')->get();
    }
}
