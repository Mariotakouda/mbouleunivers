<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\TicketService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';
    protected $description = 'Libère les billets des commandes non payées ayant expiré (RM06)';

    public function handle(TicketService $ticketService): int
    {
        $expiredOrders = Order::where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredOrders as $order) {
            $ticketService->releaseExpiredOrder($order);
            $this->info("Commande {$order->reference} expirée et libérée.");
        }

        $this->info("{$expiredOrders->count()} commande(s) traitée(s).");

        return self::SUCCESS;
    }
}
