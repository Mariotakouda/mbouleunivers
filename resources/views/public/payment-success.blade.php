@php $paid = $order?->isPaid(); @endphp
<x-layouts.app title="Paiement reçu" :back="route('home')" backLabel="Accueil">
    <div class="mx-auto max-w-lg px-4 py-14 text-center md:py-20"
         @unless ($paid)
         x-data="{ tries: Number(sessionStorage.getItem('pay_tries') || 0) }"
         x-init="if (tries < 8) { sessionStorage.setItem('pay_tries', tries + 1); setTimeout(() => location.reload(), 4000) }"
         @endunless>

        @if ($paid)
            <span class="mx-auto grid size-20 place-items-center rounded-full bg-emerald-50 text-succes ring-8 ring-emerald-50/60">
                <x-ui.icon name="check-circle" class="size-11" />
            </span>
            <h1 class="mt-6 text-3xl font-extrabold">Paiement confirmé, merci !</h1>
            <p class="mt-3 text-sourdine">Votre billet a été envoyé par email. Vous pouvez aussi le retrouver dès maintenant.</p>

            <div class="mt-8 flex flex-col gap-3">
                <a href="{{ route('ticket.show', $order->reference) }}" class="btn btn-primary btn-lg">
                    <x-ui.icon name="ticket" class="size-6" /> Voir mes billets
                </a>
                <a href="{{ route('home') }}" class="btn btn-ghost">Retour à l'accueil</a>
            </div>
            <script>sessionStorage.removeItem('pay_tries')</script>
        @else
            <span class="mx-auto grid size-20 place-items-center rounded-full bg-amber-50 text-alerte ring-8 ring-amber-50/60">
                <x-ui.icon name="loader" class="size-10 animate-spin" />
            </span>
            <h1 class="mt-6 text-3xl font-extrabold">Nous confirmons votre paiement…</h1>
            <p class="mt-3 text-sourdine">Cela prend quelques secondes. Cette page se met à jour toute seule. Vous recevrez aussi votre billet par email.</p>

            <div class="mt-8 flex flex-col gap-3">
                @if ($order)
                    <a href="{{ route('payment.show', $order->reference) }}" class="btn btn-ghost">Retour à ma commande</a>
                @endif
                <button type="button" onclick="location.reload()" class="btn btn-dark">
                    <x-ui.icon name="refresh" class="size-5" /> Vérifier maintenant
                </button>
                <a href="{{ route('home') }}" class="btn btn-ghost">Retour à l'accueil</a>
            </div>
        @endif
    </div>
</x-layouts.app>
