<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Event;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly TicketService $ticketService) {}

    public function create(Event $event): View
    {
        return view('public.checkout', compact('event'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $event = Event::findOrFail($request->input('event_id'));

        $order = $this->ticketService->createReservation(
            event: $event,
            customer: [
                'name' => $request->input('customer_name'),
                'phone' => $request->input('customer_phone'),
                'email' => $request->input('customer_email'),
            ],
            items: $request->input('items'),
        );

        return redirect()->route('payment.show', $order->reference);
    }
}
