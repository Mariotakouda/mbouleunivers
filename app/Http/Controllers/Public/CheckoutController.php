<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Event;
use App\Notifications\NewOrderNotification;
use App\Services\TicketService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly WhatsAppService $whatsApp,
    ) {}

    public function create(Event $event): View
    {
        return view('public.checkout', compact('event'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $event = Event::findOrFail($request->input('event_id'));

        try {
            $order = $this->ticketService->createReservation(
                event: $event,
                customer: [
                    'name' => $request->input('customer_name'),
                    'phone' => $this->whatsApp->e164($request->input('customer_phone')),
                    'email' => $request->input('customer_email') ?: null,
                    'note' => $request->input('customer_note') ?: null,
                ],
                items: $request->input('items'),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['items' => $e->getMessage()]);
        }

        if ($email = config('ticketing.notify_email')) {
            Notification::route('mail', $email)->notify(new NewOrderNotification($order));
        }

        return redirect()->route('order.show', $order->reference);
    }
}
