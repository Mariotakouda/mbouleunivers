<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use App\Models\EventPoster;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::with('posterMeta')->latest()->paginate(20);

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        $data['user_id'] = $request->user()->id;

        $event = Event::create($data);

        if ($request->hasFile('image')) {
            $this->savePoster($event, $request->file('image'));
        }

        return redirect()->route('admin.events.index')->with('success', 'Spectacle créé.');
    }

    public function show(Event $event): View
    {
        return view('admin.events.show', [
            'event' => $event->load('ticketTypes'),
        ]);
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(StoreEventRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        $event->update($data);

        if ($request->hasFile('image')) {
            $this->savePoster($event, $request->file('image'));
        }

        return redirect()->route('admin.events.index')->with('success', 'Spectacle mis à jour.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Spectacle supprimé.');
    }

    /** Enregistre l'affiche en base ; supprime l'éventuelle ancienne affiche stockée sur disque. */
    private function savePoster(Event $event, UploadedFile $file): void
    {
        EventPoster::updateOrCreate(
            ['event_id' => $event->id],
            ['mime' => $file->getMimeType() ?: 'image/jpeg', 'data' => base64_encode($file->get())]
        );

        if ($event->image) {
            Storage::disk('public')->delete($event->image);
            $event->forceFill(['image' => null])->save();
        }

        $event->unsetRelation('poster');
        $event->unsetRelation('posterMeta');
    }
}
