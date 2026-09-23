<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\TicketType;
use Livewire\Component;

class AdminDashboard extends Component
{
    public ?int $eventId = null;

    public function mount(?int $eventId = null): void
    {
        $this->eventId = $eventId;
    }

    public function getStatsProperty(): array
    {
        $ticketTypes = TicketType::when($this->eventId, fn ($q) => $q->where('event_id', $this->eventId))->get();
        $orders = Order::when($this->eventId, fn ($q) => $q->where('event_id', $this->eventId));

        $totalTickets = $ticketTypes->sum('quantity');
        $availableTickets = $ticketTypes->sum('available_quantity');
        $soldTickets = $totalTickets - $availableTickets;

        return [
            'total_tickets' => $totalTickets,
            'available_tickets' => $availableTickets,
            'sold_tickets' => $soldTickets,
            'reserved_orders' => (clone $orders)->where('status', 'pending')->count(),
            'entries' => Ticket::whereHas('order', fn ($q) => $this->eventId ? $q->where('event_id', $this->eventId) : $q)
                ->where('status', 'used')->count(),
            'revenue' => (clone $orders)->where('status', 'paid')->sum('total_amount'),
            'payments_successful' => Payment::whereHas('order', fn ($q) => $this->eventId ? $q->where('event_id', $this->eventId) : $q)
                ->where('status', 'successful')->count(),
            'payments_failed' => Payment::whereHas('order', fn ($q) => $this->eventId ? $q->where('event_id', $this->eventId) : $q)
                ->where('status', 'failed')->count(),
        ];
    }

    public function getRecentOrdersProperty()
    {
        return Order::when($this->eventId, fn ($q) => $q->where('event_id', $this->eventId))
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin-dashboard', [
            'stats' => $this->stats,
            'recentOrders' => $this->recentOrders,
        ]);
    }
}
