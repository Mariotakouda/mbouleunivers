<?php

namespace App\Notifications;

use App\Models\Order;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketGeneratedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->order->event;

        $mail = (new MailMessage)
            ->subject("Vos billets pour {$event->title}")
            ->greeting("Bonjour {$this->order->customer_name},")
            ->line("Votre paiement a bien été confirmé pour \"{$event->title}\".")
            ->line("Référence de commande : {$this->order->reference}")
            ->line("Date : {$event->date->format('d/m/Y')} à {$event->start_time}")
            ->line("Lieu : {$event->venue}");

        foreach ($this->order->tickets as $ticket) {
            $mail->line("Billet {$ticket->ticket_number} ({$ticket->ticketType->name})");
        }

        $mail->action('Voir mes billets', route('ticket.show', $this->order->reference))
            ->line('Présentez le QR Code de chaque billet à l\'entrée.');

        return $mail;
    }
}
