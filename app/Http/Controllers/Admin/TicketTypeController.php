<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketTypeRequest;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketTypeController extends Controller
{
    public function index(): View
    {
        $ticketTypes = TicketType::with('event')->latest()->paginate(20);

        return view('admin.ticket-types.index', compact('ticketTypes'));
    }

    public function create(Request $request): View
    {
        $events = Event::orderByDesc('date')->get();

        return view('admin.ticket-types.create', [
            'events' => $events,
            'selectedEventId' => $request->integer('event_id'),
        ]);
    }

    public function store(StoreTicketTypeRequest $request): RedirectResponse
    {
        TicketType::create($request->validated());

        return redirect()->route('admin.ticket-types.index')->with('success', 'Catégorie créée.');
    }

    public function edit(TicketType $ticketType): View
    {
        return view('admin.ticket-types.edit', [
            'ticketType' => $ticketType,
            'events' => Event::orderByDesc('date')->get(),
        ]);
    }

    public function update(StoreTicketTypeRequest $request, TicketType $ticketType): RedirectResponse
    {
        $ticketType->update($request->validated());

        return redirect()->route('admin.ticket-types.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(TicketType $ticketType): RedirectResponse
    {
        $ticketType->delete();

        return redirect()->route('admin.ticket-types.index')->with('success', 'Catégorie supprimée.');
    }
}
