<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with('event')->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load('items.ticketType', 'payments', 'tickets', 'event'),
        ]);
    }

    /**
     * Confirme à la main un paiement reçu hors ligne (Mobile Money via WhatsApp) : crée le paiement
     * et génère les billets, exactement comme le ferait le webhook FedaPay.
     */
    public function confirmManual(Order $order, TicketService $ticketService): RedirectResponse
    {
        abort_if($order->status !== 'pending', 422, 'Cette commande ne peut plus être confirmée.');

        $order->payments()->create([
            'amount' => $order->total_amount,
            'currency' => 'XOF',
            'method' => 'manual_whatsapp',
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        $ticketService->confirmPaymentAndGenerateTickets($order);

        return back()->with('success', "Paiement confirmé : les billets ont été générés et envoyés à {$order->customer_email}.");
    }
}
