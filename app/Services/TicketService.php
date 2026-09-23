<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketService
{
    public function __construct(
        private readonly QrCodeService $qrCodeService,
    ) {}

    /**
     * Crée une commande + réservation temporaire (RM04, RM05).
     * Verrouille les lignes ticket_types pour éviter la survente en cas de requêtes concurrentes.
     */
    public function createReservation(Event $event, array $customer, array $items): Order
    {
        return DB::transaction(function () use ($event, $customer, $items) {
            $totalAmount = 0;
            $preparedItems = [];

            foreach ($items as $item) {
                $ticketType = TicketType::where('id', $item['ticket_type_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$ticketType->isAvailable((int) $item['quantity'])) {
                    throw new \RuntimeException("Billets indisponibles pour \"{$ticketType->name}\".");
                }

                $subtotal = $ticketType->price * $item['quantity'];
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'ticket_type' => $ticketType,
                    'quantity' => $item['quantity'],
                    'unit_price' => $ticketType->price,
                    'subtotal' => $subtotal,
                ];

                // Réservation immédiate du stock (RM05)
                $ticketType->decrementAvailability((int) $item['quantity']);
            }

            $order = Order::create([
                'event_id' => $event->id,
                'reference' => 'ORD-' . strtoupper(Str::random(10)),
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_email' => $customer['email'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(10), // RM06
            ]);

            foreach ($preparedItems as $prepared) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $prepared['ticket_type']->id,
                    'quantity' => $prepared['quantity'],
                    'unit_price' => $prepared['unit_price'],
                    'subtotal' => $prepared['subtotal'],
                ]);
            }

            return $order;
        });
    }

    /**
     * Appelée après confirmation RÉELLE du paiement (RM07).
     * Génère les billets + QR codes (RM08, RM09), marque la commande payée.
     */
    public function confirmPaymentAndGenerateTickets(Order $order): void
    {
        DB::transaction(function () use ($order) {
            if ($order->isPaid()) {
                return; // idempotence : évite double génération si webhook + retour navigateur arrivent tous les deux
            }

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $counter = 1;
            foreach ($order->items as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    $ticket = Ticket::create([
                        'order_id' => $order->id,
                        'ticket_type_id' => $item->ticket_type_id,
                        'ticket_number' => $this->generateTicketNumber($order, $counter),
                        'qr_code' => (string) Str::uuid(),
                        'status' => 'valid',
                        'generated_at' => now(),
                    ]);

                    $this->qrCodeService->generateForTicket($ticket);
                    $counter++;
                }
            }
        });

        // Envoi email en asynchrone (hors transaction pour ne pas bloquer le commit)
        \App\Jobs\SendTicketEmailJob::dispatch($order->fresh('tickets'));
    }

    /**
     * Libère les billets d'une commande expirée (RM06) — appelée par ExpireReservations.
     */
    public function releaseExpiredOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $item->ticketType->incrementAvailability($item->quantity);
            }

            $order->update(['status' => 'expired']);
        });
    }

    private function generateTicketNumber(Order $order, int $sequence): string
    {
        return 'UM-' . str_pad((string) ($order->id * 1000 + $sequence), 6, '0', STR_PAD_LEFT);
    }
}
