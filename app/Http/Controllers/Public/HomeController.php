<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\TicketService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(TicketService $tickets): View
    {
        // Sans planificateur (hébergement gratuit), on libère les places expirées à l'ouverture de la page.
        $tickets->releaseExpiredOrdersThrottled();

        // MVP mono-spectacle : on affiche l'unique événement publié le plus récent
        $event = Event::where('status', 'published')
            ->with(['ticketTypes' => fn ($q) => $q->where('status', 'active')->orderBy('price')])
            ->latest('date')
            ->first();

        return view('public.home', compact('event'));
    }
}