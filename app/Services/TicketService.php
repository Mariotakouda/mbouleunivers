<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketService
{
    /**
     * Crée une commande + réservation temporaire (RM04, RM05).
     * Verrouille les lignes ticket_types pour éviter la survente en cas de requêtes concurrentes.
     */
    public function createReservation(Event $event, array $customer, array $items): Order
    {
        // Les places des commandes expirées redeviennent disponibles avant de réserver les nouvelles.
        $this->releaseExpiredOrders();

        $this->guardAgainstStockHoarding($customer['phone'] ?? null);

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
                'customer_email' => $customer['email'] ?? null,
                'customer_note' => $customer['note'] ?? null,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                // RM06 : sans paiement en ligne, on garde les places le temps de l'échange sur WhatsApp.
                'expires_at' => now()->addHours(config('ticketing.hold_hours', 24)),
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
                return; // idempotence : évite de générer deux fois les billets si l'admin double-clique
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

                    $counter++;
                }
            }
        });

        // Envoi email en asynchrone (hors transaction pour ne pas bloquer le commit).
        // L'email est facultatif : sans adresse, l'admin envoie le lien des billets par WhatsApp.
        if ($order->customer_email) {
            try {
                \App\Jobs\SendTicketEmailJob::dispatch($order->fresh('tickets'));
            } catch (\Throwable $e) {
                // Avec QUEUE_CONNECTION=sync, un email qui échoue ne doit jamais empêcher la confirmation du paiement.
                report($e);
            }
        }
    }

    /**
     * L'admin a reçu l'argent (Flooz, T-Money, espèces…) : on enregistre l'encaissement puis on
     * génère les billets. Idempotent : une commande déjà payée n'est pas retraitée.
     */
    public function markAsPaid(Order $order, string $method, ?string $reference = null): void
    {
        DB::transaction(function () use ($order, $method, $reference) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            abort_if($locked->status !== 'pending', 422, 'Cette commande ne peut plus être confirmée.');

            Payment::create([
                'order_id' => $locked->id,
                'transaction_id' => $reference ?: null,
                'amount' => $locked->total_amount,
                'currency' => 'XOF',
                'method' => $method,
                'status' => 'successful',
                'paid_at' => now(),
            ]);
        });

        $this->confirmPaymentAndGenerateTickets($order->fresh('items'));
    }

    /** Annulation par l'admin : les places retournent en vente. */
    public function cancelOrder(Order $order): void
    {
        $this->release($order, 'cancelled');
    }

    /** Laisse plus de temps au client pour payer (admin). */
    public function extendReservation(Order $order, int $hours = 24): void
    {
        abort_if($order->status !== 'pending', 422, 'Seule une commande en attente peut être prolongée.');

        $base = $order->expires_at && $order->expires_at->isFuture() ? $order->expires_at : now();

        $order->update(['expires_at' => $base->copy()->addHours($hours)]);
    }

    /**
     * Sans paiement en ligne, rien ne « coûte » au client de réserver : on limite donc le nombre de
     * commandes en attente par numéro pour éviter qu'une seule personne bloque toute la salle.
     */
    private function guardAgainstStockHoarding(?string $phone): void
    {
        if (! $phone) {
            return;
        }

        $max = (int) config('ticketing.max_pending_per_phone', 2);

        if ($max > 0 && Order::awaiting()->where('customer_phone', $phone)->count() >= $max) {
            throw new \RuntimeException(
                "Vous avez déjà {$max} commandes en attente avec ce numéro. Finalisez-les sur WhatsApp, ou contactez-nous pour en ajouter."
            );
        }
    }

    /** Libère toutes les commandes en attente dont le délai est dépassé. Retourne leur nombre. */
    public function releaseExpiredOrders(): int
    {
        $orders = Order::where('status', 'pending')->where('expires_at', '<=', now())->get();

        foreach ($orders as $order) {
            $this->releaseExpiredOrder($order);
        }

        return $orders->count();
    }

    /**
     * Même chose, au plus une fois par minute : appelée à l'ouverture des pages, pour que ça fonctionne
     * même sans planificateur (hébergement gratuit, où rien ne tourne en arrière-plan).
     */
    public function releaseExpiredOrdersThrottled(): void
    {
        if (\Illuminate\Support\Facades\Cache::add('orders:release-expired', true, 60)) {
            $this->releaseExpiredOrders();
        }
    }

    /**
     * Libère les billets d'une commande expirée (RM06) — appelée par ExpireReservations.
     */
    public function releaseExpiredOrder(Order $order): void
    {
        $this->release($order, 'expired');
    }

    private function release(Order $order, string $finalStatus): void
    {
        DB::transaction(function () use ($order, $finalStatus) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                return; // déjà payée, annulée ou expirée : on ne libère jamais deux fois les places
            }

            foreach ($locked->items()->with('ticketType')->get() as $item) {
                $item->ticketType?->incrementAvailability($item->quantity);
            }

            $locked->update([
                'status' => $finalStatus,
                'cancelled_at' => $finalStatus === 'cancelled' ? now() : $locked->cancelled_at,
            ]);
        });

        $order->refresh();
    }

    private function generateTicketNumber(Order $order, int $sequence): string
    {
        return 'UM-' . str_pad((string) ($order->id * 1000 + $sequence), 6, '0', STR_PAD_LEFT);
    }
}
