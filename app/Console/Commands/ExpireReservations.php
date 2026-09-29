<?php

namespace App\Console\Commands;

use App\Services\TicketService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';
    protected $description = 'Libère les billets des commandes non payées ayant expiré (RM06)';

    public function handle(TicketService $ticketService): int
    {
        $count = $ticketService->releaseExpiredOrders();

        $this->info("{$count} commande(s) expirée(s) et libérée(s).");

        return self::SUCCESS;
    }
}
