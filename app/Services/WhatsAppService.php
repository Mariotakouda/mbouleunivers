<?php

namespace App\Services;

use App\Models\Order;

/**
 * Tout ce qui touche à WhatsApp : numéros, liens wa.me et messages préremplis.
 * Aucun appel réseau : on ouvre simplement WhatsApp avec un texte prêt à envoyer.
 */
class WhatsAppService
{
    /** Numéro WhatsApp de l'organisateur (chiffres uniquement, indicatif inclus) ou null s'il n'est pas configuré. */
    public function supportNumber(): ?string
    {
        return $this->digits((string) config('services.support.whatsapp'));
    }

    public function isConfigured(): bool
    {
        return $this->supportNumber() !== null;
    }

    /**
     * Normalise un numéro saisi par un client : « 90 12 34 56 » → « 22890123456 ».
     * Un numéro à 8 chiffres est considéré comme togolais ; un numéro international (+33…) est conservé.
     */
    public function digits(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) === 8 ? '228' . $digits : $digits;
    }

    /** Forme stockée en base : « +22890123456 ». */
    public function e164(?string $phone): ?string
    {
        $digits = $this->digits($phone);

        return $digits ? '+' . $digits : null;
    }

    /** Forme lisible : « +228 90 12 34 56 ». */
    public function pretty(?string $phone): string
    {
        $digits = $this->digits($phone);

        if (! $digits) {
            return (string) $phone;
        }

        if (str_starts_with($digits, '228') && strlen($digits) === 11) {
            return '+228 ' . trim(chunk_split(substr($digits, 3), 2, ' '));
        }

        return '+' . $digits;
    }

    public function url(?string $number, string $message): ?string
    {
        $digits = $this->digits($number);

        return $digits ? 'https://wa.me/' . $digits . '?text=' . rawurlencode($message) : null;
    }

    /** Lien WhatsApp vers l'organisateur avec la commande prête à envoyer. */
    public function orderUrlForOrganizer(Order $order): ?string
    {
        return $this->url($this->supportNumber(), $this->orderMessage($order));
    }

    /** Lien WhatsApp de l'admin vers le client (relance / instructions de paiement). */
    public function customerUrl(Order $order): ?string
    {
        return $this->url($order->customer_phone, $this->messageToCustomer($order));
    }

    /** Lien WhatsApp de l'admin vers le client avec le lien de ses billets. */
    public function ticketsUrl(Order $order): ?string
    {
        return $this->url($order->customer_phone, $this->ticketsMessage($order));
    }

    /** Message que le client envoie à l'organisateur : tout est déjà dedans, il n'a qu'à appuyer sur « Envoyer ». */
    public function orderMessage(Order $order): string
    {
        $order->loadMissing('event', 'items.ticketType');
        $event = $order->event;

        $lines = [
            "🎭 *NOUVELLE COMMANDE — {$order->reference}*",
            '',
            "*{$event->title}*",
            "📅 {$event->dateLong()} à {$event->startTimeLabel()}",
        ];

        if ($event->venue) {
            $lines[] = "📍 {$event->venue}";
        }

        $lines[] = '';
        $lines[] = '🎟️ *Billets*';

        foreach ($order->items as $item) {
            $lines[] = "• {$item->quantity} × " . ($item->ticketType?->name ?? 'Billet') . ' — ' . $this->money($item->subtotal);
        }

        $lines[] = '';
        $lines[] = '💰 *Total : ' . $this->money($order->total_amount) . '*';
        $lines[] = '';
        $lines[] = '👤 *Client*';
        $lines[] = "Nom : {$order->customer_name}";
        $lines[] = 'Téléphone : ' . $this->pretty($order->customer_phone);

        if ($order->customer_email) {
            $lines[] = "Email : {$order->customer_email}";
        }

        if ($order->customer_note) {
            $lines[] = "Message : {$order->customer_note}";
        }

        $lines[] = '';
        $lines[] = 'Bonjour, je confirme ma commande. Pouvez-vous m\'indiquer comment procéder au paiement (Flooz / T-Money) ? Merci !';

        return implode("\n", $lines);
    }

    /** Premier message de l'admin au client. */
    public function messageToCustomer(Order $order): string
    {
        $order->loadMissing('event', 'items.ticketType');

        $tickets = $order->items
            ->map(fn ($item) => "{$item->quantity} × " . ($item->ticketType?->name ?? 'Billet'))
            ->implode(', ');

        return "Bonjour {$order->customer_name}, c'est l'équipe « {$order->event->title} ». "
            . "Nous avons bien reçu votre commande {$order->reference} ({$tickets}, total {$this->money($order->total_amount)}). "
            . 'Vous pouvez régler par Mobile Money (Flooz ou T-Money) ; dès réception, nous vous envoyons vos billets avec QR code.';
    }

    /** Message de l'admin au client une fois le paiement confirmé. */
    public function ticketsMessage(Order $order): string
    {
        $order->loadMissing('event');

        return "Bonjour {$order->customer_name}, votre paiement est confirmé ✅ "
            . "Voici vos billets pour « {$order->event->title} » ({$order->event->dateLong()}) : "
            . route('ticket.show', $order->reference)
            . " — présentez le QR code à l'entrée. À très bientôt !";
    }

    private function money(float|int|string $amount): string
    {
        return number_format((float) $amount, 0, ',', ' ') . ' FCFA';
    }
}
