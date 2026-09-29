@php
    $whatsApp = app(\App\Services\WhatsAppService::class);
    $paid = $order->isPaid();
    $waiting = $order->isAwaitingConfirmation();
    $closed = ! $paid && ! $waiting; // expirée ou annulée
@endphp
<x-layouts.app :title="'Commande '.$order->reference" :back="route('events.show', $order->event)" backLabel="Retour au spectacle">
    <div class="border-b border-bord bg-white">
        <div class="mx-auto max-w-2xl px-4 py-4">
            <x-ui.stepper :current="$paid ? 4 : 3" />
        </div>
    </div>

    <div class="mx-auto max-w-2xl px-4 py-8 md:py-12"
         @if ($waiting) x-data x-init="setTimeout(() => location.reload(), 60000)" @endif>

        @if (session('error'))
            <x-ui.alert type="error" class="mb-6">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- ============ En-tête selon l'état de la commande ============ --}}
        @if ($paid)
            <div class="text-center">
                <span class="mx-auto grid size-20 place-items-center rounded-full bg-emerald-50 text-succes ring-8 ring-emerald-50/60">
                    <x-ui.icon name="check-circle" class="size-11" />
                </span>
                <h1 class="mt-6 text-3xl font-extrabold">Commande confirmée, merci !</h1>
                <p class="mt-3 text-sourdine">Votre paiement a bien été reçu. Vos billets avec QR code sont prêts.</p>
                <a href="{{ route('ticket.show', $order->reference) }}" class="btn btn-primary btn-lg mt-8 w-full">
                    <x-ui.icon name="ticket" class="size-6" /> Voir mes billets
                </a>
            </div>
        @elseif ($waiting)
            <h1 class="text-3xl font-extrabold">Commande enregistrée&nbsp;!</h1>
            <p class="mt-2 text-sourdine">
                Référence <strong class="text-nuit">{{ $order->reference }}</strong> · {{ $order->customer_name }}.
                Dernière étape : envoyez-la-nous sur WhatsApp pour que nous la confirmions.
            </p>

            @if ($whatsappConfigured)
                <a href="{{ route('order.whatsapp', $order->reference) }}" target="_blank" rel="noopener"
                   style="background-color:#25D366;color:#fff;border-color:#25D366" class="btn btn-lg mt-6 w-full hover:brightness-95">
                    <x-ui.icon name="message-circle" class="size-6" /> Envoyer ma commande sur WhatsApp
                </a>
                <p class="mt-2 text-center text-sm text-sourdine">Le message est déjà rédigé : il vous suffit d'appuyer sur « Envoyer ».</p>

                @if ($order->whatsapp_clicked_at)
                    <x-ui.alert type="success" class="mt-4">
                        WhatsApp a été ouvert le {{ $order->whatsapp_clicked_at->format('d/m à H:i') }}. Si le message n'est pas parti, appuyez de nouveau sur le bouton ci-dessus.
                    </x-ui.alert>
                @endif
            @else
                <x-ui.alert type="info" class="mt-6">
                    <p class="font-semibold">Votre commande est bien enregistrée.</p>
                    <p class="mt-0.5">Nous vous contactons très vite au {{ $whatsApp->pretty($order->customer_phone) }} pour la confirmer.</p>
                </x-ui.alert>
            @endif

            <div class="card mt-6 p-5 sm:p-6">
                <h2 class="text-lg font-extrabold">Et ensuite ?</h2>
                <ol class="mt-4 space-y-4 text-sm">
                    @foreach ([
                        ['Vous nous écrivez', 'Le message contient déjà votre commande et vos coordonnées.'],
                        ['Nous confirmons et vous indiquons comment payer', 'Par Mobile Money (Flooz ou T-Money), directement avec nous.'],
                        ['Vous recevez vos billets', 'Avec QR code, sur cette page et sur WhatsApp.'],
                    ] as [$title, $text])
                        <li class="flex items-start gap-3">
                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-nuit text-xs font-bold text-white">{{ $loop->iteration }}</span>
                            <span><strong class="block text-nuit">{{ $title }}</strong><span class="text-sourdine">{{ $text }}</span></span>
                        </li>
                    @endforeach
                </ol>
            </div>

            <p class="mt-4 flex items-start gap-2 text-sm text-sourdine">
                <x-ui.icon name="clock" class="mt-0.5 size-4 shrink-0" />
                Vos places sont gardées jusqu'au {{ $order->expires_at->format('d/m/Y à H:i') }}. Passé ce délai sans confirmation, elles sont remises en vente.
            </p>
        @else
            <x-ui.alert type="error">
                <p class="font-semibold">{{ $order->status === 'cancelled' ? 'Cette commande a été annulée.' : 'Le délai de réservation est écoulé.' }}</p>
                <p class="mt-1">Les places ont été remises en vente. Vous pouvez refaire une commande.</p>
                <a href="{{ route('events.show', $order->event) }}" class="btn btn-dark btn-sm mt-3">Recommencer</a>
            </x-ui.alert>
        @endif

        {{-- ============ Récapitulatif ============ --}}
        <div class="card mt-8 overflow-hidden">
            <div class="stage px-5 py-4 text-white">
                <p class="font-display text-lg font-extrabold leading-tight">{{ $order->event->title }}</p>
                <p class="mt-1 text-sm text-white/75">{{ $order->event->dateLong() }} · {{ $order->event->startTimeLabel() }} · {{ $order->event->venue }}</p>
            </div>
            <div class="space-y-3 p-5">
                @foreach ($order->items as $item)
                    <div class="flex items-start justify-between gap-3 text-sm">
                        <span><strong>{{ $item->quantity }} ×</strong> {{ $item->ticketType?->name ?? 'Billet' }}</span>
                        <span class="font-semibold tabular-nums">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                @endforeach
                <div class="flex items-baseline justify-between border-t border-bord pt-4">
                    <span class="font-semibold">{{ $paid ? 'Montant réglé' : 'Total à régler' }}</span>
                    <span class="font-display text-3xl font-extrabold tabular-nums">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
            <dl class="grid gap-3 border-t border-bord bg-craie/60 p-5 text-sm sm:grid-cols-2">
                <div><dt class="text-sourdine">Nom</dt><dd class="font-semibold">{{ $order->customer_name }}</dd></div>
                <div><dt class="text-sourdine">WhatsApp</dt><dd class="font-semibold">{{ $whatsApp->pretty($order->customer_phone) }}</dd></div>
                @if ($order->customer_email)
                    <div class="sm:col-span-2"><dt class="text-sourdine">Email</dt><dd class="break-all font-semibold">{{ $order->customer_email }}</dd></div>
                @endif
                @if ($order->customer_note)
                    <div class="sm:col-span-2"><dt class="text-sourdine">Votre message</dt><dd class="font-semibold">{{ $order->customer_note }}</dd></div>
                @endif
            </dl>
        </div>

        <p class="mt-6 text-center text-sm text-sourdine">
            Gardez cette page : elle affiche toujours l'état de votre commande.
        </p>
    </div>
</x-layouts.app>
