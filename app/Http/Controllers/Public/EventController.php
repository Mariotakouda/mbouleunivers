<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function show(Event $event): View
    {
        abort_unless($event->isPublished(), 404);

        return view('public.event-show', compact('event'));
    }
}
