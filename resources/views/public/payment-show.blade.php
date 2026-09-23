@php
    $pending = $order->status === 'pending';
    $remaining = (int) max(0, now()->diffInSeconds($order->expires_at, false));
@endphp
<x-layouts.app title="Paiement" :back="route('events.show', $order->event)" backLabel="Retour au spectacle">
    <div class="border-b border-bord bg-white">
        <div class="mx-auto max-w-2xl px-4 py-4">
            <x-ui.stepper :current="$order->status === 'paid' ? 4 : 3" />
        </div>
    </div>

    <div class="mx-auto max-w-2xl px-4 py-8 md:py-12">
        <h1 class="text-3xl font-extrabold">Finalisez votre paiement</h1>
        <p class="mt-2 text-sourdine">Commande <strong class="text-nuit">{{ $order->reference }}</strong> · {{ $order->customer_name }}</p>

        {{-- Compte à rebours de la réservation --}}
        @if ($pending)
            <div x-data="{
                    remaining: {{ $remaining }},
                    init() { setInterval(() => { if (this.remaining > 0) this.remaining-- }, 1000) },
                    get label() { const m = Math.floor(this.remaining / 60), s = this.remaining % 60; return m + ' min ' + String(s).padStart(2, '0') + ' s' },
                    get urgent() { return this.remaining <= 120 },
                 }"
                 class="mt-6">
                <div x-show="remaining > 0" class="flex items-center justify-between gap-4 rounded-xl border px-4 py-3"
                     :class="urgent ? 'border-alerte/40 bg-amber-50 text-amber-900' : 'border-bord bg-white'">
                    <span class="flex items-center gap-2.5 text-sm font-medium">
                        <x-ui.icon name="clock" class="size-5" /> Vos places sont réservées encore
                    </span>
                    <span class="font-display text-xl font-extrabold tabular-nums" x-text="label">{{ intdiv($remaining, 60) }} min</span>
                </div>

                <div x-show="remaining <= 0" x-cloak>
                    <x-ui.alert type="error">
                        <p class="font-semibold">Le délai de réservation est écoulé.</p>
                        <p class="mt-1">Vos places ont été libérées. Vous pouvez recommencer votre réservation.</p>
                        <a href="{{ route('events.show', $order->event) }}" class="btn btn-dark btn-sm mt-3">Recommencer</a>
                    </x-ui.alert>
                </div>
            </div>
        @endif

        {{-- Récapitulatif --}}
        <div class="card mt-6 overflow-hidden">
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
                    <span class="font-semibold">Montant à payer</span>
                    <span class="font-display text-3xl font-extrabold tabular-nums">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        {{-- Action --}}
        <div class="mt-6">
            @if (session('error'))
                <x-ui.alert type="error" class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif
            @if ($pending)
                <div x-data="{ mode: 'online' }">
                    {{-- Choix du mode de paiement --}}
                    <div class="grid grid-cols-2 gap-3" role="radiogroup" aria-label="Mode de paiement">
                        <button type="button" @click="mode = 'online'" role="radio" :aria-checked="mode === 'online'"
                                class="flex min-h-14 items-center justify-center gap-2 rounded-xl border-2 px-3 text-sm font-bold transition-colors"
                                :class="mode === 'online' ? 'border-nuit bg-nuit text-white' : 'border-bord bg-white text-nuit'">
                            <x-ui.icon name="credit-card" class="size-5" /> Payer en ligne
                        </button>
                        <button type="button" @click="mode = 'whatsapp'" role="radio" :aria-checked="mode === 'whatsapp'"
                                class="flex min-h-14 items-center justify-center gap-2 rounded-xl border-2 px-3 text-sm font-bold transition-colors"
                                :class="mode === 'whatsapp' ? 'border-succes bg-succes text-white' : 'border-bord bg-white text-nuit'">
                            <x-ui.icon name="message-circle" class="size-5" /> Payer par WhatsApp
                        </button>
                    </div>

                    {{-- Option 1 : paiement en ligne (PayGateGlobal) --}}
                    <div x-show="mode === 'online'" class="mt-5">
                        <form method="POST" action="{{ route('payment.initiate', $order->reference) }}" x-data="{ busy: false }" @submit="busy = true">
                            @csrf
                            <button type="submit" :disabled="busy" class="btn btn-success btn-lg w-full">
                                <x-ui.icon name="loader" class="size-5 animate-spin" x-show="busy" x-cloak />
                                <x-ui.icon name="lock" class="size-5" x-show="!busy" />
                                <span x-text="busy ? 'Redirection vers la page de paiement…' : 'Payer {{ number_format($order->total_amount, 0, ',', ' ') }} FCFA'">Payer en ligne</span>
                            </button>
                        </form>
                        <ul class="mt-5 space-y-2 text-sm text-sourdine">
                            <li class="flex items-start gap-2"><x-ui.icon name="shield-check" class="mt-0.5 size-4 shrink-0 text-succes" /> Vous serez redirigé vers la page de paiement sécurisée PayGateGlobal (Flooz, T-Money).</li>
                            <li class="flex items-start gap-2"><x-ui.icon name="mail" class="mt-0.5 size-4 shrink-0 text-succes" /> Votre billet sera envoyé à {{ $order->customer_email }} dès que le paiement est confirmé.</li>
                        </ul>
                    </div>

                    {{-- Option 2 : WhatsApp (paiement Mobile Money direct, sans frais) --}}
                    <div x-show="mode === 'whatsapp'" x-cloak class="mt-5">
                        <x-ui.alert type="info" class="mb-4">
                            Vous payez directement par Mobile Money, sans passer par la passerelle en ligne : pas de frais supplémentaires.
                            Vos billets vous seront envoyés dès que nous aurons confirmé la réception du paiement.
                        </x-ui.alert>
                        <form method="POST" action="{{ route('payment.manual', $order->reference) }}" x-data="{ busy: false }" @submit="busy = true">
                            @csrf
                            <button type="submit" :disabled="busy" class="btn btn-lg w-full bg-[#25D366] text-white hover:brightness-95">
                                <x-ui.icon name="loader" class="size-5 animate-spin" x-show="busy" x-cloak />
                                <x-ui.icon name="message-circle" class="size-5" x-show="!busy" />
                                <span x-text="busy ? 'Ouverture de WhatsApp…' : 'Continuer sur WhatsApp'">Continuer sur WhatsApp</span>
                            </button>
                        </form>
                        <ul class="mt-5 space-y-2 text-sm text-sourdine">
                            <li class="flex items-start gap-2"><x-ui.icon name="clock" class="mt-0.5 size-4 shrink-0 text-succes" /> Vos places restent réservées 2 heures, le temps de finaliser avec nous.</li>
                            <li class="flex items-start gap-2"><x-ui.icon name="message-circle" class="mt-0.5 size-4 shrink-0 text-succes" /> Un message avec votre commande s'ouvre automatiquement dans WhatsApp.</li>
                        </ul>
                    </div>
                </div>
            @elseif ($order->status === 'paid')
                <a href="{{ route('ticket.show', $order->reference) }}" class="btn btn-primary btn-lg w-full">
                    <x-ui.icon name="ticket" class="size-6" /> Voir mes billets
                </a>
            @else
                <x-ui.alert type="error">
                    Cette réservation n'est plus valide.
                    <a href="{{ route('events.show', $order->event) }}" class="font-semibold underline">Recommencer une réservation</a>
                </x-ui.alert>
            @endif
        </div>
    </div>
</x-layouts.app>
