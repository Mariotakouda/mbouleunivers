<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TicketService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    /** Onglets de la liste : clé => [libellé, filtre]. */
    private const TABS = [
        'all' => 'Toutes',
        'pending' => 'À traiter',
        'paid' => 'Payées',
        'closed' => 'Expirées / annulées',
    ];

    public function index(Request $request): View
    {
        $tab = array_key_exists($request->query('statut'), self::TABS) ? $request->query('statut') : 'all';
        $search = trim((string) $request->query('q'));

        $orders = Order::with('event', 'items.ticketType')
            ->when($tab === 'pending', fn ($q) => $q->awaiting())
            ->when($tab === 'paid', fn ($q) => $q->where('status', 'paid'))
            ->when($tab === 'closed', fn ($q) => $q->where(fn ($q) => $q
                ->whereIn('status', ['expired', 'cancelled'])
                ->orWhere(fn ($q) => $q->where('status', 'pending')->where('expires_at', '<=', now()))))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $digits = preg_replace('/\D/', '', $search);

                $q->where(function ($q) use ($like, $digits) {
                    $q->where('reference', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('customer_email', 'like', $like);

                    if (strlen($digits) >= 4) {
                        $q->orWhere('customer_phone', 'like', "%{$digits}%");
                    }
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => Order::count(),
            'pending' => Order::awaiting()->count(),
            'paid' => Order::where('status', 'paid')->count(),
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'tabs' => self::TABS,
            'tab' => $tab,
            'search' => $search,
            'counts' => $counts,
            'whatsApp' => app(WhatsAppService::class),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load('items.ticketType', 'payments', 'tickets.ticketType', 'event'),
            'whatsApp' => app(WhatsAppService::class),
            'paymentMethods' => config('ticketing.payment_methods'),
        ]);
    }

    /**
     * L'organisateur a reçu l'argent (Flooz, T-Money, espèces…) : on enregistre l'encaissement
     * et on génère les billets. Le client les retrouve sur sa page de commande.
     */
    public function confirmManual(Request $request, Order $order, TicketService $ticketService): RedirectResponse
    {
        abort_if($order->status !== 'pending', 422, 'Cette commande ne peut plus être confirmée.');

        $data = $request->validate([
            'method' => ['required', Rule::in(array_keys(config('ticketing.payment_methods')))],
            'reference' => ['nullable', 'string', 'max:100', 'unique:payments,transaction_id'],
        ], [
            'reference.unique' => 'Cette référence de transaction est déjà enregistrée sur une autre commande.',
        ]);

        $ticketService->markAsPaid($order, $data['method'], $data['reference'] ?? null);

        $message = $order->customer_email
            ? "Paiement confirmé : les billets ont été générés et envoyés à {$order->customer_email}. Vous pouvez aussi les envoyer par WhatsApp."
            : 'Paiement confirmé : les billets ont été générés. Envoyez-les au client par WhatsApp avec le bouton ci-dessous.';

        return back()->with('success', $message);
    }

    public function extend(Order $order, TicketService $ticketService): RedirectResponse
    {
        $hours = (int) config('ticketing.hold_hours', 24);

        $ticketService->extendReservation($order, $hours);

        return back()->with('success', "Réservation prolongée de {$hours} h.");
    }

    public function cancel(Order $order, TicketService $ticketService): RedirectResponse
    {
        abort_if($order->status !== 'pending', 422, 'Seule une commande en attente peut être annulée.');

        $ticketService->cancelOrder($order);

        return back()->with('success', 'Commande annulée : les places sont de nouveau disponibles.');
    }
}
