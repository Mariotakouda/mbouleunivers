<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderNotification;
use App\Notifications\TicketGeneratedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class SendTicketEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $log = OrderNotification::create([
            'order_id' => $this->order->id,
            'type' => 'ticket_generated',
            'channel' => 'email',
            'recipient' => $this->order->customer_email,
            'status' => 'pending',
        ]);

        try {
            NotificationFacade::route('mail', $this->order->customer_email)
                ->notify(new TicketGeneratedNotification($this->order));

            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed']);
            throw $e; // laisse la queue retenter selon $tries
        }
    }

    public function failed(\Throwable $exception): void
    {
        OrderNotification::where('order_id', $this->order->id)
            ->where('type', 'ticket_generated')
            ->latest()
            ->first()
            ?->update(['status' => 'failed']);
    }
}
