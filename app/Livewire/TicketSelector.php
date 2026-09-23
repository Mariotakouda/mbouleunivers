<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;

class TicketSelector extends Component
{
    /** Nombre maximum de billets par catégorie et par commande. */
    private const MAX_PER_TYPE = 10;

    public Event $event;

    /** [ticket_type_id => quantité] — sert à pré-remplir la sélection (retour depuis l'étape suivante). */
    public array $quantities = [];

    public function mount(Event $event): void
    {
        $this->event = $event;

        $previous = collect(session('checkout_items', []))->pluck('quantity', 'ticket_type_id');

        foreach ($this->sellableTypes() as $type) {
            $max = $this->maxFor($type);
            $this->quantities[$type->id] = min((int) ($previous[$type->id] ?? 0), $max);
        }
    }

    /**
     * La sélection est gérée côté navigateur (Alpine) pour une réaction instantanée,
     * même avec une connexion lente. Le serveur revalide tout avant de continuer.
     */
    public function proceedToCheckout(array $selection = [])
    {
        $items = $this->sellableTypes()
            ->map(function ($type) use ($selection) {
                $qty = max(0, min((int) ($selection[$type->id] ?? 0), $this->maxFor($type)));

                return $qty > 0 ? ['ticket_type_id' => $type->id, 'quantity' => $qty] : null;
            })
            ->filter()
            ->values()
            ->all();

        if (empty($items)) {
            $this->addError('selection', 'Sélectionnez au moins un billet pour continuer.');

            return null;
        }

        session(['checkout_items' => $items]);

        return $this->redirect(route('checkout.create', $this->event), navigate: true);
    }

    private function sellableTypes()
    {
        return $this->event->sellableTicketTypes();
    }

    private function maxFor($type): int
    {
        return max(0, min(self::MAX_PER_TYPE, (int) $type->available_quantity));
    }

    public function render()
    {
        $types = $this->sellableTypes();

        return view('livewire.ticket-selector', [
            'types' => $types,
            'maxPerType' => self::MAX_PER_TYPE,
            'config' => [
                'q' => (object) $this->quantities,
                'types' => (object) $types->mapWithKeys(fn ($t) => [
                    $t->id => ['price' => (float) $t->price, 'max' => $this->maxFor($t)],
                ])->all(),
            ],
        ]);
    }
}
