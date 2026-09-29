<?php

namespace App\Livewire;

use App\Models\Order;
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
            'reserved_orders' => (clone $orders)->awaiting()->count(),
            'confirmed_orders' => (clone $orders)->where('status', 'paid')->count(),
            'entries' => Ticket::whereHas('order', fn ($q) => $this->eventId ? $q->where('event_id', $this->eventId) : $q)
                ->where('status', 'used')->count(),
            'revenue' => (clone $orders)->where('status', 'paid')->sum('total_amount'),
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
        app(\App\Services\TicketService::class)->releaseExpiredOrdersThrottled();

        return view('livewire.admin-dashboard', [
            'stats' => $this->stats,
            'recentOrders' => $this->recentOrders,
        ]);
    }
}
