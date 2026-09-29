<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Prévient l'organisateur par email qu'une commande vient d'arriver (en plus de la liste admin et de WhatsApp). */
class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order->loadMissing('event', 'items.ticketType');
        $whatsApp = app(WhatsAppService::class);

        $mail = (new MailMessage)
            ->subject("Nouvelle commande {$order->reference} — " . number_format((float) $order->total_amount, 0, ',', ' ') . ' FCFA')
            ->greeting('Nouvelle commande à traiter')
            ->line("Client : {$order->customer_name} ({$whatsApp->pretty($order->customer_phone)})");

        foreach ($order->items as $item) {
            $mail->line("• {$item->quantity} × " . ($item->ticketType?->name ?? 'Billet'));
        }

        if ($order->customer_note) {
            $mail->line("Message du client : {$order->customer_note}");
        }

        return $mail->action('Ouvrir la commande', route('admin.orders.show', $order));
    }
}
