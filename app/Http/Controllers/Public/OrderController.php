<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly WhatsAppService $whatsApp) {}

    /**
     * Page de suivi de la commande : « envoyez-la sur WhatsApp », puis « confirmée » avec le lien des billets.
     * La référence (10 caractères aléatoires) sert de clé d'accès : le client peut y revenir à tout moment.
     */
    public function show(Order $order): View
    {
        $order->load('event', 'items.ticketType');

        return view('public.order-show', [
            'order' => $order,
            'whatsappConfigured' => $this->whatsApp->isConfigured(),
        ]);
    }

    /**
     * Ouvre WhatsApp avec la commande prête à envoyer. On note au passage que le client a bien
     * ouvert WhatsApp, pour que l'admin sache qui a envoyé son message et qui n'a pas terminé.
     */
    public function whatsapp(Order $order): RedirectResponse
    {
        if (! $order->isAwaitingConfirmation()) {
            return redirect()->route('order.show', $order->reference);
        }

        $url = $this->whatsApp->orderUrlForOrganizer($order);

        if (! $url) {
            return redirect()
                ->route('order.show', $order->reference)
                ->with('error', "L'envoi par WhatsApp n'est pas disponible pour le moment. Votre commande est bien enregistrée : nous vous contacterons.");
        }

        $order->forceFill(['whatsapp_clicked_at' => now()])->save();

        return redirect()->away($url);
    }
}
