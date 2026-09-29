@php
    use Illuminate\Support\Str;

    $types = $event?->ticketTypes ?? collect();
    $minPrice = $types->isNotEmpty() ? (float) $types->min('price') : null;
    $soldOut = $types->isNotEmpty() && $types->sum('available_quantity') <= 0;
    $over = $event?->isOver();
    $canBuy = $event && !$over && !$soldOut && $types->isNotEmpty();
    $countdown = $event?->countdownLabel();
    $shareText = $event ? "Réserve ta place pour {$event->title} — {$event->dateLong()}" : null;
@endphp

<x-layouts.app
    :description="$event ? Str::limit($event->description, 155) : null"
    :image="$event?->posterUrl() ? url($event->posterUrl()) : null"
    :sticky="$canBuy">

@if (! $event)
    <section class="mx-auto flex max-w-xl flex-col items-center px-4 py-24 text-center">
        <span class="grid size-16 place-items-center rounded-2xl bg-white text-sourdine ring-1 ring-bord">
            <x-ui.icon name="calendar" class="size-8" />
        </span>
        <h1 class="mt-6 text-3xl font-extrabold">Aucun spectacle en vente pour le moment</h1>
        <p class="mt-3 text-sourdine">La billetterie ouvrira dès que la prochaine date sera confirmée. Revenez bientôt.</p>
    </section>
@else
    {{-- ============ Hero ============ --}}
    <section class="stage text-white">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-12 md:grid-cols-[1.15fr_0.85fr] md:py-20">
            <div>
                @if ($countdown)
                    <p class="mb-5 inline-flex items-center gap-2 rounded-full bg-safran px-3.5 py-1.5 text-sm font-bold text-nuit">
                        <x-ui.icon name="clock" class="size-4" /> {{ $countdown }}
                    </p>
                @elseif ($over)
                    <p class="mb-5 inline-flex rounded-full bg-white/15 px-3.5 py-1.5 text-sm font-bold">Spectacle terminé</p>
                @endif

                <h1 class="text-[2.75rem] font-extrabold leading-[1.02] sm:text-6xl lg:text-7xl">{{ $event->title }}</h1>

                <p class="mt-5 max-w-xl text-lg leading-relaxed text-white/80">{{ Str::limit($event->description, 220) }}</p>

                <ul class="mt-7 flex flex-wrap gap-x-7 gap-y-3 text-[1.05rem] font-medium">
                    <li class="flex items-center gap-2.5"><x-ui.icon name="calendar" class="size-5 text-safran" /> {{ $event->dateLong() }}</li>
                    <li class="flex items-center gap-2.5"><x-ui.icon name="clock" class="size-5 text-safran" /> {{ $event->startTimeLabel() }}</li>
                    <li class="flex items-center gap-2.5"><x-ui.icon name="map-pin" class="size-5 text-safran" /> {{ $event->venue }}</li>
                </ul>

                <div class="mt-9 flex flex-wrap items-center gap-3">
                    @if ($canBuy)
                        <a href="{{ route('events.show', $event) }}" class="btn btn-primary btn-lg">
                            <x-ui.icon name="ticket" class="size-6" /> Acheter mes billets
                        </a>
                    @elseif ($soldOut && ! $over)
                        <span class="btn btn-lg cursor-not-allowed bg-white/15 text-white">Complet</span>
                    @endif
                    <x-ui.share-button tone="dark" :title="$event->title" :text="$shareText" class="btn-lg" />
                </div>

                @if ($canBuy && $minPrice !== null)
                    <p class="mt-4 text-sm text-white/70">À partir de <strong class="text-white">{{ number_format($minPrice, 0, ',', ' ') }} FCFA</strong> par personne</p>
                @endif
            </div>

            <div>
                @if ($event->hasPoster())
                    <img src="{{ $event->posterUrl() }}" alt="Affiche : {{ $event->title }}"
                         class="mx-auto max-h-[34rem] w-auto rounded-3xl object-cover shadow-2xl shadow-black/40 ring-1 ring-white/15">
                @else
                    <div class="relative mx-auto w-full max-w-sm rounded-[2rem] border border-white/15 bg-white/[0.06] p-8">
                        <x-ui.icon name="mic" class="absolute right-7 top-7 size-10 text-safran/70" />
                        <p class="font-display text-8xl font-extrabold leading-none text-safran">{{ $event->date->format('d') }}</p>
                        <p class="mt-2 font-display text-3xl font-bold capitalize">{{ $event->date->copy()->locale('fr')->translatedFormat('F') }}</p>
                        <p class="mt-1 text-white/70">{{ $event->date->copy()->locale('fr')->translatedFormat('l Y') }}</p>
                        <div class="my-6 border-t border-dashed border-white/25"></div>
                        <p class="text-lg font-semibold">{{ $event->startTimeLabel() }}@if ($event->endTimeLabel()) – {{ $event->endTimeLabel() }}@endif</p>
                        <p class="mt-1 text-white/70">{{ $event->venue }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ============ Catégories de billets ============ --}}
    @if ($types->isNotEmpty())
        <section id="places" class="mx-auto max-w-6xl px-4 py-14">
            <h2 class="text-3xl font-extrabold">Choisissez votre place</h2>
            <p class="mt-2 text-sourdine">Tous les prix sont en FCFA.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($types as $type)
                    @php
                        $avail = (int) $type->available_quantity;
                        $low = $avail > 0 && $avail <= max(5, (int) ceil($type->quantity * 0.1));
                    @endphp
                    <a href="{{ route('events.show', $event) }}"
                       @class([
                           'card group flex flex-col p-6 transition-colors',
                           'hover:border-nuit' => $avail > 0,
                           'pointer-events-none opacity-60' => $avail <= 0 || $over,
                       ])>
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-xl font-bold">{{ $type->name }}</h3>
                            @if ($avail <= 0)
                                <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-erreur ring-1 ring-inset ring-erreur/20">Complet</span>
                            @elseif ($low)
                                <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-alerte ring-1 ring-inset ring-alerte/20">Plus que {{ $avail }}</span>
                            @endif
                        </div>
                        @if ($type->description)
                            <p class="mt-2 text-sm leading-relaxed text-sourdine">{{ $type->description }}</p>
                        @endif
                        <p class="mt-auto pt-6 font-display text-3xl font-extrabold">
                            {{ number_format($type->price, 0, ',', ' ') }} <span class="text-base font-semibold text-sourdine">FCFA</span>
                        </p>
                    </a>
                @endforeach
            </div>

            @if ($canBuy)
                <div class="mt-8">
                    <a href="{{ route('events.show', $event) }}" class="btn btn-dark btn-lg">
                        Réserver maintenant <x-ui.icon name="arrow-right" class="size-5" />
                    </a>
                </div>
            @endif
        </section>
    @endif

    {{-- ============ Comment ça marche ============ --}}
    <section class="border-y border-bord bg-white">
        <div class="mx-auto max-w-6xl px-4 py-14">
            <h2 class="text-3xl font-extrabold">Réserver en 3 étapes</h2>
            <ol class="mt-8 grid gap-8 md:grid-cols-3">
                @foreach ([
                    ['Choisissez vos billets', 'Sélectionnez la catégorie et le nombre de places. Pas besoin de créer un compte.'],
                    ['Envoyez votre commande', 'Votre commande arrive directement chez nous et s\'ouvre dans WhatsApp. Nous confirmons, vous réglez par Mobile Money (Flooz, T-Money) et vos places sont gardées le temps de payer.'],
                    ['Recevez votre QR code', 'Votre billet arrive sur WhatsApp (et par email si vous le souhaitez) et reste disponible en ligne. Présentez-le à l\'entrée.'],
                ] as $i => [$stepTitle, $stepText])
                    <li class="flex gap-4">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-safran font-display text-lg font-extrabold text-nuit">{{ $i + 1 }}</span>
                        <div>
                            <h3 class="text-lg font-bold">{{ $stepTitle }}</h3>
                            <p class="mt-1 leading-relaxed text-sourdine">{{ $stepText }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============ Infos pratiques ============ --}}
    <section id="infos" class="mx-auto max-w-6xl px-4 py-14">
        <div class="card grid gap-8 p-6 md:grid-cols-2 md:p-9">
            <div>
                <h2 class="text-3xl font-extrabold">Infos pratiques</h2>
                <dl class="mt-6 space-y-5">
                    <div class="flex gap-4">
                        <x-ui.icon name="calendar" class="mt-0.5 size-6 shrink-0 text-safran-vif" />
                        <div><dt class="text-sm text-sourdine">Date</dt><dd class="font-semibold">{{ $event->dateLong() }}</dd></div>
                    </div>
                    <div class="flex gap-4">
                        <x-ui.icon name="clock" class="mt-0.5 size-6 shrink-0 text-safran-vif" />
                        <div><dt class="text-sm text-sourdine">Horaires</dt><dd class="font-semibold">{{ $event->startTimeLabel() }}@if ($event->endTimeLabel()) – {{ $event->endTimeLabel() }}@endif</dd></div>
                    </div>
                    <div class="flex gap-4">
                        <x-ui.icon name="map-pin" class="mt-0.5 size-6 shrink-0 text-safran-vif" />
                        <div><dt class="text-sm text-sourdine">Lieu</dt><dd class="font-semibold">{{ $event->venue }}@if ($event->address)<span class="block font-normal text-sourdine">{{ $event->address }}</span>@endif</dd></div>
                    </div>
                </dl>
            </div>
            <div class="flex flex-col justify-center gap-4 rounded-xl bg-craie p-6">
                <p class="text-lg font-semibold">Besoin de trouver la salle ?</p>
                <p class="text-sourdine">Ouvrez l'itinéraire dans votre application de cartes.</p>
                <a href="{{ $event->mapUrl() }}" target="_blank" rel="noopener" class="btn btn-ghost self-start">
                    <x-ui.icon name="map-pin" class="size-5" /> Voir sur la carte
                </a>
            </div>
        </div>
    </section>

    {{-- ============ Questions fréquentes ============ --}}
    <section class="mx-auto max-w-6xl px-4 pb-16">
        <h2 class="text-3xl font-extrabold">Questions fréquentes</h2>
        <div class="mt-6 max-w-3xl space-y-3">
            @foreach ([
                ['Comment je reçois mon billet ?', "Dès que nous avons confirmé votre paiement, votre billet avec QR code s'affiche sur la page de votre commande et vous est envoyé sur WhatsApp (et par email si vous en avez indiqué un). Vous pouvez aussi le télécharger en PDF."],
                ['Dois-je créer un compte ?', 'Non. Il suffit d\'indiquer votre nom et votre numéro WhatsApp au moment de la commande (l\'email est facultatif).'],
                ['Comment se passe le paiement ?', 'Aucun paiement n\'est demandé sur le site. Une fois votre commande envoyée sur WhatsApp, nous vous indiquons comment régler par Mobile Money (Flooz ou T-Money). Vos places sont gardées le temps de finaliser ; passé ce délai, elles sont remises en vente.'],
                ['Comment entrer dans la salle ?', 'Présentez le QR code de votre billet (sur téléphone ou imprimé) à l\'agent à l\'entrée. Chaque billet ne peut être scanné qu\'une seule fois.'],
            ] as [$q, $a])
                <details class="group card p-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold [&::-webkit-details-marker]:hidden">
                        {{ $q }}
                        <x-ui.icon name="chevron-down" class="size-5 shrink-0 text-sourdine transition-transform group-open:rotate-180" />
                    </summary>
                    <p class="mt-3 leading-relaxed text-sourdine">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- ============ Barre d'achat collante (mobile) ============ --}}
    @if ($canBuy)
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-bord bg-white/95 px-4 pt-3 backdrop-blur md:hidden"
             style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
            <div class="mx-auto flex max-w-6xl items-center gap-4">
                @if ($minPrice !== null)
                    <div class="leading-tight">
                        <p class="text-xs text-sourdine">À partir de</p>
                        <p class="font-display text-lg font-extrabold">{{ number_format($minPrice, 0, ',', ' ') }} FCFA</p>
                    </div>
                @endif
                <a href="{{ route('events.show', $event) }}" class="btn btn-primary ml-auto flex-1 sm:max-w-xs">Acheter mes billets</a>
            </div>
        </div>
    @endif
@endif
</x-layouts.app>
