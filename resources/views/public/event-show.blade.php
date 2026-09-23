@php $over = $event->isOver(); @endphp
<x-layouts.app
    :title="$event->title"
    :description="\Illuminate\Support\Str::limit($event->description, 155)"
    :image="$event->image ? url(Storage::url($event->image)) : null"
    :back="route('home')"
    backLabel="Accueil"
    :sticky="! $over">

    <div class="border-b border-bord bg-white">
        <div class="mx-auto max-w-6xl px-4 py-4">
            <x-ui.stepper :current="1" />
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8 md:py-12">
        <div class="grid gap-8 md:grid-cols-[1fr_26rem] md:gap-x-12 lg:grid-cols-[1fr_28rem]">
            {{-- Titre et infos clés (en premier, aussi sur mobile) --}}
            <div class="md:col-start-1 md:row-start-1">
                <h1 class="text-4xl font-extrabold leading-tight sm:text-5xl">{{ $event->title }}</h1>

                <ul class="mt-5 space-y-2.5 text-[1.05rem]">
                    <li class="flex items-center gap-3"><x-ui.icon name="calendar" class="size-5 text-safran-vif" /> <span class="font-medium">{{ $event->dateLong() }}</span></li>
                    <li class="flex items-center gap-3"><x-ui.icon name="clock" class="size-5 text-safran-vif" />
                        <span class="font-medium">{{ $event->startTimeLabel() }}@if ($event->endTimeLabel()) – {{ $event->endTimeLabel() }}@endif</span>
                    </li>
                    <li class="flex items-start gap-3"><x-ui.icon name="map-pin" class="mt-1 size-5 shrink-0 text-safran-vif" />
                        <span><span class="font-medium">{{ $event->venue }}</span>@if ($event->address)<span class="text-sourdine"> — {{ $event->address }}</span>@endif</span>
                    </li>
                </ul>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ $event->mapUrl() }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">
                        <x-ui.icon name="map-pin" class="size-4" /> Itinéraire
                    </a>
                    <x-ui.share-button class="btn-sm" :title="$event->title" :text="'Réserve ta place pour '.$event->title.' — '.$event->dateLong()" />
                </div>
            </div>

            {{-- Sélection des billets --}}
            <div class="md:col-start-2 md:row-span-2 md:row-start-1">
                <div class="md:sticky md:top-24">
                    @if ($over)
                        <x-ui.alert type="warning">Ce spectacle est terminé. La vente de billets est fermée.</x-ui.alert>
                    @else
                        @livewire('ticket-selector', ['event' => $event])
                    @endif
                </div>
            </div>

            {{-- Affiche et description --}}
            <div class="md:col-start-1 md:row-start-2">
                @if ($event->image)
                    <img src="{{ Storage::url($event->image) }}" alt="Affiche : {{ $event->title }}" class="w-full max-w-md rounded-2xl ring-1 ring-bord">
                @endif

                <div class="mt-8 max-w-prose">
                    <h2 class="text-xl font-bold">À propos du spectacle</h2>
                    <p class="mt-2 whitespace-pre-line leading-relaxed text-sourdine">{{ $event->description }}</p>
                </div>

                <x-ui.alert type="info" class="mt-8 max-w-prose">
                    Après votre choix, vous aurez 10 minutes pour payer. Le billet avec QR code est ensuite envoyé par email.
                </x-ui.alert>
            </div>
        </div>
    </div>
</x-layouts.app>
