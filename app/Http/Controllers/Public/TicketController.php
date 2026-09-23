<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function show(Order $order): View
    {
        abort_unless($order->isPaid(), 404);

        return view('public.ticket', [
            'order' => $order->load('tickets.ticketType', 'event'),
        ]);
    }

    public function download(Ticket $ticket): Response
    {
        $ticket->load('ticketType', 'order.event');

        abort_unless($ticket->order->isPaid(), 404);

        $pdf = Pdf::loadView('pdf.ticket', compact('ticket'));

        return $pdf->download("{$ticket->ticket_number}.pdf");
    }
}
