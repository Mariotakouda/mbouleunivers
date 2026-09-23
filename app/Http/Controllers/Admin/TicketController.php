<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(): View
    {
        $tickets = Ticket::with('ticketType', 'order')->latest()->paginate(20);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket): View
    {
        return view('admin.tickets.show', [
            'ticket' => $ticket->load('ticketType', 'order', 'scans.user'),
        ]);
    }

    public function cancel(Ticket $ticket): RedirectResponse
    {
        $ticket->update(['status' => 'cancelled']); // RM12

        return back()->with('success', 'Billet annulé.');
    }
}
