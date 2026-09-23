<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // MVP mono-spectacle : on affiche l'unique événement publié le plus récent
        $event = Event::where('status', 'published')
            ->with(['ticketTypes' => fn ($q) => $q->where('status', 'active')->orderBy('price')])
            ->latest('date')
            ->first();

        return view('public.home', compact('event'));
    }
}