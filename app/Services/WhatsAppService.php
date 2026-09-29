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

    /**
     * Message que le client envoie à l'organisateur. Tout est déjà dedans (commande + coordonnées) :
     * il n'a qu'à appuyer sur « Envoyer ». Mise en forme WhatsApp : *gras*, sans surcharge d'emojis.
     */
    public function orderMessage(Order $order): string
    {
        $order->loadMissing('event', 'items.ticketType');
        $event = $order->event;

        $lines = [
            "*NOUVELLE COMMANDE — {$order->reference}*",
            "Passée le {$order->created_at->format('d/m/Y')} à {$order->created_at->format('H:i')}",
            '',
            "*{$event->title}*",
            $this->eventWhen($event),
        ];

        if ($event->venue) {
            $lines[] = "Lieu : {$event->venue}";
        }

        $lines[] = '';
        $lines[] = '*Détail de la commande*';

        foreach ($order->items as $item) {
            $lines[] = "▪ {$item->quantity} × " . ($item->ticketType?->name ?? 'Billet')
                . ' (' . $this->money($item->unit_price) . ') = ' . $this->money($item->subtotal);
        }

        $lines[] = '*Total à régler : ' . $this->money($order->total_amount) . '*';
        $lines[] = '';
        $lines[] = '*Coordonnées du client*';
        $lines[] = "Nom : {$order->customer_name}";
        $lines[] = 'WhatsApp : ' . $this->pretty($order->customer_phone);

        if ($order->customer_email) {
            $lines[] = "Email : {$order->customer_email}";
        }

        if ($order->customer_note) {
            $lines[] = '';
            $lines[] = '*Message du client*';
            $lines[] = $order->customer_note;
        }

        $lines[] = '';
        $lines[] = 'Bonjour, je souhaite confirmer cette commande. Pourriez-vous m\'indiquer la marche à suivre pour le règlement par Mobile Money (Flooz ou T-Money) ? Je vous remercie.';

        return implode("\n", $lines);
    }

    /** Premier message de l'admin au client : accusé de réception + comment payer. */
    public function messageToCustomer(Order $order): string
    {
        $order->loadMissing('event', 'items.ticketType');
        $event = $order->event;

        $lines = [
            "Bonjour {$order->customer_name},",
            '',
            "Merci pour votre commande *{$order->reference}* pour « {$event->title} » : nous l'avons bien reçue.",
            '',
            '*Récapitulatif*',
        ];

        foreach ($order->items as $item) {
            $lines[] = "▪ {$item->quantity} × " . ($item->ticketType?->name ?? 'Billet') . ' — ' . $this->money($item->subtotal);
        }

        $lines[] = '*Total à régler : ' . $this->money($order->total_amount) . '*';
        $lines[] = '';
        $lines[] = '*Pour régler par Mobile Money*';

        $methods = $this->paymentInstructions();

        if ($methods) {
            foreach ($methods as $method) {
                $lines[] = "▪ {$method}";
            }
            $lines[] = "Merci d'indiquer la référence {$order->reference} et de nous envoyer la capture de confirmation.";
        } else {
            $lines[] = 'Réglez par Flooz ou T-Money, puis envoyez-nous la capture de confirmation en indiquant la référence ' . $order->reference . '.';
        }

        if ($order->expires_at) {
            $lines[] = '';
            $lines[] = "Vos places sont réservées jusqu'au {$order->expires_at->format('d/m/Y')} à {$order->expires_at->format('H:i')}.";
        }

        $lines[] = '';
        $lines[] = "Dès réception de votre paiement, nous vous envoyons vos billets avec QR code.";
        $lines[] = "L'équipe {$event->title}";

        return implode("\n", $lines);
    }

    /** Message de l'admin au client une fois le paiement confirmé, avec le lien de ses billets. */
    public function ticketsMessage(Order $order): string
    {
        $order->loadMissing('event');
        $event = $order->event;

        $lines = [
            "Bonjour {$order->customer_name},",
            '',
            "Nous avons bien reçu votre paiement de *{$this->money($order->total_amount)}* : votre commande *{$order->reference}* est confirmée. Merci !",
            '',
            "*{$event->title}*",
            $this->eventWhen($event),
        ];

        if ($event->venue) {
            $lines[] = "Lieu : {$event->venue}";
        }

        $lines[] = '';
        $lines[] = '*Vos billets (QR code)*';
        $lines[] = route('ticket.show', $order->reference);
        $lines[] = '';
        $lines[] = "Présentez le QR code de chaque billet à l'entrée (sur téléphone ou imprimé). Chaque billet n'est valable qu'une seule fois : ne le partagez pas publiquement.";
        $lines[] = '';
        $lines[] = 'À très bientôt !';
        $lines[] = "L'équipe {$event->title}";

        return implode("\n", $lines);
    }

    /** Lignes « Flooz : 90 00 00 00 (Nom) » configurées par l'organisateur ; vide si rien n'est configuré. */
    private function paymentInstructions(): array
    {
        $name = config('ticketing.payment_account_name');
        $lines = [];

        foreach (['flooz' => 'Flooz (Moov Money)', 'tmoney' => 'T-Money (Togocom)'] as $key => $label) {
            $number = config("ticketing.payment_numbers.{$key}");

            if ($number) {
                $lines[] = "{$label} : " . $this->pretty($number) . ($name ? " — au nom de {$name}" : '');
            }
        }

        return $lines;
    }

    /** « Samedi 17 octobre 2026 à 19h00 » */
    private function eventWhen($event): string
    {
        return mb_strtoupper(mb_substr($event->dateLong(), 0, 1)) . mb_substr($event->dateLong(), 1)
            . " à {$event->startTimeLabel()}";
    }

    private function money(float|int|string $amount): string
    {
        return number_format((float) $amount, 0, ',', ' ') . ' FCFA';
    }
}
