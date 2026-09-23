<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PayGateService;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PayGateService $payGateService,
        private readonly TicketService $ticketService,
    ) {}

    public function show(Order $order): View
    {
        abort_if($order->isExpired(), 410, 'Cette réservation a expiré.');

        $order->load('event', 'items.ticketType');

        return view('public.payment-show', compact('order'));
    }

    /**
     * Le client choisit de payer hors ligne (Mobile Money direct) et d'en informer le vendeur par WhatsApp,
     * pour éviter les frais FedaPay. On prolonge la réservation pour laisser le temps à l'échange, puis on
     * ouvre WhatsApp avec un message pré-rempli. Le paiement est ensuite confirmé À LA MAIN par l'admin.
     */
    public function manual(Order $order): RedirectResponse
    {
        abort_if($order->isExpired(), 410, 'Cette réservation a expiré.');
        abort_unless($order->status === 'pending', 404);

        $whatsapp = preg_replace('/\D/', '', (string) config('services.support.whatsapp'));

        if (! $whatsapp) {
            return redirect()
                ->route('payment.show', $order->reference)
                ->with('error', "Le paiement par WhatsApp n'est pas disponible pour le moment. Merci de payer en ligne.");
        }

        // On laisse largement le temps à l'échange WhatsApp avant de libérer les places (RM06).
        $order->update(['expires_at' => now()->addHours(2)]);

        $lines = $order->items->map(
            fn ($item) => "- {$item->quantity} x {$item->ticketType?->name} (" . number_format($item->subtotal, 0, ',', ' ') . ' FCFA)'
        )->implode("\n");

        $message = "Bonjour, je souhaite payer ma commande {$order->reference} pour \"{$order->event->title}\" par Mobile Money.\n\n"
            . "{$lines}\n\nTotal : " . number_format($order->total_amount, 0, ',', ' ') . " FCFA\n"
            . "Nom : {$order->customer_name}\nTéléphone : {$order->customer_phone}";

        return redirect()->away("https://wa.me/{$whatsapp}?text=" . rawurlencode($message));
    }

    public function initiate(Order $order): RedirectResponse
    {
        abort_if($order->isExpired(), 410, 'Cette réservation a expiré.');

        try {
            $paymentUrl = $this->payGateService->paymentUrl($order);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('payment.show', $order->reference)
                ->with('error', "Le paiement en ligne est momentanément indisponible. Vos places restent réservées : réessayez dans un instant.");
        }

        // On garde une trace de la tentative : PayGateGlobal ne renvoie l'identifiant de transaction
        // qu'une fois le paiement effectué (webhook), donc rien d'autre à stocker pour l'instant.
        $order->payments()->updateOrCreate(
            ['order_id' => $order->id],
            ['amount' => $order->total_amount, 'currency' => 'XOF', 'method' => 'paygate', 'status' => 'pending']
        );

        return redirect()->away($paymentUrl);
    }

    /**
     * Webhook PayGateGlobal — SEULE source de vérité pour confirmer un paiement (RM07).
     * PayGateGlobal poste { identifier, tx_reference, payment_reference, amount, datetime,
     * payment_method, phone_number }. On ne fait JAMAIS confiance à ce contenu brut (rien ne le
     * signe) : on revérifie toujours le statut auprès de PayGateGlobal via son API avant de générer
     * les billets.
     */
    public function webhook(Request $request): \Illuminate\Http\Response
    {
        $identifier = $request->input('identifier');
        $order = $identifier ? Order::where('reference', $identifier)->first() : null;

        if (! $order) {
            return response()->noContent(404);
        }

        try {
            $status = $this->payGateService->checkStatus($identifier);
            $verified = $this->payGateService->isSuccessful($status);
        } catch (\Throwable $e) {
            report($e);
            $verified = false;
        }

        if (! $verified) {
            return response()->noContent(400);
        }

        $order->payments()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'transaction_id' => $status['tx_reference'] ?? $request->input('tx_reference'),
                'amount' => $order->total_amount,
                'currency' => 'XOF',
                'method' => 'paygate',
                'status' => 'successful',
                'paid_at' => now(),
            ]
        );

        $this->ticketService->confirmPaymentAndGenerateTickets($order);

        return response()->noContent();
    }

    /**
     * Retour navigateur après PayGateGlobal (paramètre "url"). Ce retour n'a AUCUN pouvoir de
     * confirmation : la vue se contente d'afficher le statut actuel de la commande en base, et se
     * recharge toute seule le temps que le webhook (seule source de vérité, RM07) confirme le paiement.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $order = $request->filled('ref')
            ? Order::with('event')->where('reference', $request->query('ref'))->first()
            : null;

        return view('public.payment-success', compact('order'));
    }

    public function failed(Request $request): View
    {
        $order = $request->filled('ref')
            ? Order::where('reference', $request->query('ref'))->first()
            : null;

        return view('public.payment-failed', compact('order'));
    }
}
