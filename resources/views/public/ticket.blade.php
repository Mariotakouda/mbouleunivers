@php
    $event = $order->event;
    $count = $order->tickets->count();
    $shareText = "Mes billets pour {$event->title} — {$event->dateLong()} : " . url()->current();
@endphp
<x-layouts.app title="Mes billets" :back="route('home')" backLabel="Accueil">
    <div class="mx-auto max-w-2xl px-4 py-8 md:py-12">
        <h1 class="text-3xl font-extrabold">Vos billets</h1>
        <p class="mt-2 text-sourdine">
            Commande <strong class="text-nuit">{{ $order->reference }}</strong>
            · {{ $count }} {{ $count > 1 ? 'billets' : 'billet' }}
        </p>

        <x-ui.alert type="info" class="mt-6">
            <p class="font-semibold">À l'entrée</p>
            <p class="mt-0.5">Présentez le QR code de chaque billet. Montez la luminosité de votre écran et pensez à faire une capture d'écran au cas où le réseau serait faible.</p>
        </x-ui.alert>

        <div class="mt-5 flex flex-wrap gap-3">
            <a class="btn btn-ghost btn-sm" target="_blank" rel="noopener"
               href="https://wa.me/?text={{ rawurlencode($shareText) }}">
                <x-ui.icon name="message" class="size-4" /> Envoyer par WhatsApp
            </a>
            <button type="button" class="btn btn-ghost btn-sm" onclick="window.print()">
                <x-ui.icon name="download" class="size-4" /> Imprimer
            </button>
        </div>

        <div class="mt-8 space-y-6">
            @foreach ($order->tickets as $ticket)
                <article class="relative overflow-visible rounded-3xl border border-bord bg-white shadow-sm print:break-inside-avoid">
                    {{-- Haut du billet --}}
                    <div class="stage rounded-t-3xl px-6 py-5 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="font-display text-2xl font-extrabold leading-tight">{{ $event->title }}</h2>
                                <p class="mt-1 text-sm text-white/75">Billet {{ $loop->iteration }} sur {{ $count }}</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-safran px-3 py-1 text-sm font-bold text-nuit">{{ $ticket->ticketType->name }}</span>
                        </div>
                    </div>

                    <div class="grid gap-5 px-6 py-6 sm:grid-cols-2">
                        <dl class="space-y-3 text-sm">
                            <div><dt class="text-sourdine">Nom</dt><dd class="font-semibold">{{ $order->customer_name }}</dd></div>
                            <div><dt class="text-sourdine">Date</dt><dd class="font-semibold">{{ $event->dateLong() }}</dd></div>
                            <div><dt class="text-sourdine">Heure</dt><dd class="font-semibold">{{ $event->startTimeLabel() }}</dd></div>
                            <div><dt class="text-sourdine">Lieu</dt><dd class="font-semibold">{{ $event->venue }}</dd></div>
                        </dl>

                        <div class="flex flex-col items-center justify-center">
                            <img src="{{ asset('storage/qrcodes/' . $ticket->qr_code . '.svg') }}"
                                 alt="QR code du billet {{ $ticket->ticket_number }}"
                                 class="size-44 rounded-xl border border-bord p-2">
                            <p class="mt-2 text-xs tracking-wide text-sourdine">{{ $ticket->ticket_number }}</p>
                        </div>
                    </div>

                    {{-- Perforation --}}
                    <div class="relative border-t-2 border-dashed border-bord">
                        <span class="absolute -left-3.5 -top-3.5 size-7 rounded-full bg-craie ring-1 ring-bord [clip-path:inset(0_0_0_50%)]"></span>
                        <span class="absolute -right-3.5 -top-3.5 size-7 rounded-full bg-craie ring-1 ring-bord [clip-path:inset(0_50%_0_0)]"></span>
                    </div>

                    <div class="flex items-center justify-between gap-3 px-6 py-4">
                        <p class="flex items-center gap-2 text-sm text-sourdine">
                            <x-ui.icon name="shield-check" class="size-4 text-succes" /> Une seule entrée par billet
                        </p>
                        <a href="{{ route('ticket.download', $ticket->ticket_number) }}" class="btn btn-dark btn-sm print:hidden">
                            <x-ui.icon name="download" class="size-4" /> PDF
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</x-layouts.app>
