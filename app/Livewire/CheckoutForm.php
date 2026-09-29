<?php

namespace App\Livewire;

use App\Models\Event;
use App\Notifications\NewOrderNotification;
use App\Services\TicketService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class CheckoutForm extends Component
{
    public Event $event;
    public array $items = [];

    public string $customerName = '';
    public string $customerPhone = '';
    public string $customerEmail = '';
    public string $customerNote = '';

    protected function rules(): array
    {
        return [
            'customerName' => ['required', 'string', 'max:150'],
            // 8 chiffres (Togo), avec +228 / 00228, ou un numéro international (+33…) pour la diaspora.
            'customerPhone' => ['required', 'string', 'max:20', 'regex:/^((\+?228|00228)?[0-9]{8}|\+[1-9][0-9]{7,14})$/'],
            'customerEmail' => ['nullable', 'email', 'max:150'],
            'customerNote' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected array $messages = [
        'customerName.required' => 'Indiquez votre nom complet.',
        'customerPhone.required' => 'Indiquez votre numéro WhatsApp.',
        'customerPhone.regex' => 'Numéro invalide : saisissez 8 chiffres (ex. 90 12 34 56), ou un numéro international (+33…).',
        'customerEmail.email' => 'Cette adresse email ne semble pas valide.',
        'customerNote.max' => 'Votre message est trop long (500 caractères maximum).',
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

    public function submit(TicketService $ticketService, WhatsAppService $whatsApp)
    {
        $this->customerPhone = $this->cleanPhone($this->customerPhone);
        $this->validate();

        // Sans paiement en ligne, on freine les commandes en rafale depuis une même connexion.
        $throttleKey = 'checkout:' . request()->ip();
        $maxPerIp = (int) config('ticketing.max_orders_per_ip', 5);

        if ($maxPerIp > 0 && RateLimiter::tooManyAttempts($throttleKey, $maxPerIp)) {
            $this->addError('items', 'Trop de commandes en peu de temps. Patientez quelques minutes ou contactez-nous sur WhatsApp.');

            return;
        }

        try {
            $order = $ticketService->createReservation(
                event: $this->event,
                customer: [
                    'name' => trim($this->customerName),
                    'phone' => $whatsApp->e164($this->customerPhone),
                    'email' => trim($this->customerEmail) ?: null,
                    'note' => trim($this->customerNote) ?: null,
                ],
                items: $this->items,
            );
        } catch (\RuntimeException $e) {
            $this->addError('items', $e->getMessage());

            return;
        }

        RateLimiter::hit($throttleKey, 600);

        if ($email = config('ticketing.notify_email')) {
            try {
                Notification::route('mail', $email)->notify(new NewOrderNotification($order));
            } catch (\Throwable $e) {
                report($e); // un email en panne ne doit jamais bloquer la commande du client
            }
        }

        session()->forget('checkout_items');

        return $this->redirect(route('order.show', $order->reference), navigate: true);
    }

    /** « 90 12 34 56 » → « 90123456 » */
    private function cleanPhone(string $phone): string
    {
        return preg_replace('/[\s.\-()]/', '', $phone);
    }

    public function render()
    {
        return view('livewire.checkout-form', [
            'holdHours' => (int) config('ticketing.hold_hours', 24),
        ]);
    }
}
