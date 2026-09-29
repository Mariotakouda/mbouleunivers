<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\TicketService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EventController extends Controller
{
    public function show(Event $event, TicketService $tickets): View
    {
        abort_unless($event->isPublished(), 404);

        $tickets->releaseExpiredOrdersThrottled();

        return view('public.event-show', compact('event'));
    }

    /** Affiche du spectacle, servie depuis la base (le disque de l'hébergeur gratuit est éphémère). */
    public function poster(Event $event): Response
    {
        abort_unless($event->isPublished(), 404);

        $poster = $event->poster;
        abort_unless($poster && ($binary = $poster->binary()) !== '', 404);

        return response($binary, 200, [
            'Content-Type' => $poster->mime,
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
