<?php

namespace App\Livewire;

use App\Models\Event;
use App\Services\TicketService;
use Livewire\Component;

class CheckoutForm extends Component
{
    public Event $event;
    public array $items = [];

    public string $customerName = '';
    public string $customerPhone = '';
    public string $customerEmail = '';

    protected function rules(): array
    {
        return [
            'customerName' => ['required', 'string', 'max:150'],
            'customerPhone' => ['required', 'string', 'max:20', 'regex:/^(\+228)?[0-9]{8}$/'],
            'customerEmail' => ['required', 'email', 'max:150'],
        ];
    }

    protected array $messages = [
        'customerName.required' => 'Indiquez votre nom complet.',
        'customerPhone.required' => 'Indiquez votre numéro de téléphone.',
        'customerPhone.regex' => 'Numéro invalide : saisissez 8 chiffres (ex. 90 12 34 56) ou le format +228 90 12 34 56.',
        'customerEmail.required' => 'Indiquez votre adresse email : c\'est là que nous enverrons vos billets.',
        'customerEmail.email' => 'Cette adresse email ne semble pas valide.',
    ];

    public function mount(Event $event): void
    {
        $this->event = $event;
        $this->items = session('checkout_items', []);

        if (empty($this->items)) {
            $this->redirect(route('events.show', $event), navigate: true);
        }
    }

    /** Validation champ par champ dès que la personne quitte le champ. */
    public function updated(string $property): void
    {
        if ($property === 'customerPhone') {
            $this->customerPhone = $this->cleanPhone($this->customerPhone);
        }

        $this->validateOnly($property);
    }

    public function getSelectedTicketsProperty()
    {
        $types = $this->event->ticketTypes()->get()->keyBy('id');

        return collect($this->items)
            ->map(function ($item) use ($types) {
                $ticketType = $types->get($item['ticket_type_id']);

                if (! $ticketType) {
                    return null;
                }

                return [
                    'name' => $ticketType->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $ticketType->price,
                    'subtotal' => $ticketType->price * $item['quantity'],
                ];
            })
            ->filter()
            ->values();
    }

    public function getTotalProperty(): float
    {
        return (float) $this->selectedTickets->sum('subtotal');
    }

    public function getTicketCountProperty(): int
    {
        return (int) $this->selectedTickets->sum('quantity');
    }

    public function submit(TicketService $ticketService)
    {
        $this->customerPhone = $this->cleanPhone($this->customerPhone);
        $this->validate();

        try {
            $order = $ticketService->createReservation(
                event: $this->event,
                customer: [
                    'name' => $this->customerName,
                    'phone' => $this->customerPhone,
                    'email' => $this->customerEmail,
                ],
                items: $this->items,
            );
        } catch (\RuntimeException $e) {
            $this->addError('items', $e->getMessage());
            return;
        }

        session()->forget('checkout_items');

        return $this->redirect(route('payment.show', $order->reference), navigate: true);
    }

    /** « 90 12 34 56 » → « 90123456 » */
    private function cleanPhone(string $phone): string
    {
        return preg_replace('/[\s.\-()]/', '', $phone);
    }

    public function render()
    {
        return view('livewire.checkout-form');
    }
}
