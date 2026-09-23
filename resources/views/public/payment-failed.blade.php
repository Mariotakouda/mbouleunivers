@php $canRetry = $order && $order->status === 'pending' && ! $order->isExpired(); @endphp
<x-layouts.app title="Paiement non abouti" :back="$order ? route('events.show', $order->event_id) : route('home')" :backLabel="$order ? 'Retour au spectacle' : 'Accueil'">
    <div class="mx-auto max-w-lg px-4 py-14 text-center md:py-20">
        <span class="mx-auto grid size-20 place-items-center rounded-full bg-red-50 text-erreur ring-8 ring-red-50/60">
            <x-ui.icon name="x-circle" class="size-11" />
        </span>
        <h1 class="mt-6 text-3xl font-extrabold">Le paiement n'a pas abouti</h1>
        <p class="mt-3 text-sourdine">
            @if ($canRetry)
                Vos billets restent réservés pendant quelques minutes : vous pouvez réessayer.
            @else
                Vos places ont peut-être été libérées, vous pouvez recommencer la réservation.
            @endif
        </p>

        @if ($order)
            <p class="mt-4 text-sm text-sourdine">
                Si un montant a été débité malgré tout, conservez votre référence de commande :
                <strong class="text-nuit">{{ $order->reference }}</strong>
            </p>
        @endif

        <div class="mt-8 flex flex-col gap-3">
            @if ($canRetry)
                <a href="{{ route('payment.show', $order->reference) }}" class="btn btn-primary btn-lg">
                    <x-ui.icon name="refresh" class="size-5" /> Réessayer le paiement
                </a>
            @elseif ($order)
                <a href="{{ route('events.show', $order->event_id) }}" class="btn btn-primary btn-lg">Recommencer ma réservation</a>
            @endif
            <a href="{{ route('home') }}" class="btn btn-ghost">Retour à l'accueil</a>
        </div>
    </div>
</x-layouts.app>
