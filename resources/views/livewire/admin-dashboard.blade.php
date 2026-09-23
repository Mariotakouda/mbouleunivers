@php
    $sold = (int) $stats['sold_tickets'];
    $total = (int) $stats['total_tickets'];
    $pct = $total > 0 ? (int) round($sold / $total * 100) : 0;
    $kpis = [
        ['Billets vendus', $sold, 'ticket', 'bg-indigo-50 text-indigo-700'],
        ['Places restantes', $stats['available_tickets'], 'users', 'bg-emerald-50 text-succes'],
        ['Réservations en attente', $stats['reserved_orders'], 'clock', 'bg-amber-50 text-alerte'],
        ['Entrées scannées', $stats['entries'], 'qr', 'bg-sky-50 text-sky-700'],
    ];
@endphp
<div class="space-y-6" wire:poll.30s>
    {{-- Chiffre d'affaires + avancement des ventes --}}
    <div class="stage grid gap-6 rounded-2xl p-6 text-white md:grid-cols-[1fr_1.2fr] md:items-center md:p-8">
        <div>
            <p class="flex items-center gap-2 text-sm font-medium text-white/75"><x-ui.icon name="banknote" class="size-5 text-safran" /> Chiffre d'affaires</p>
            <p class="mt-2 font-display text-4xl font-extrabold leading-none sm:text-5xl">
                {{ number_format($stats['revenue'], 0, ',', ' ') }} <span class="text-xl font-bold text-white/70">FCFA</span>
            </p>
            <p class="mt-3 text-sm text-white/70">
                {{ $stats['payments_successful'] }} {{ $stats['payments_successful'] > 1 ? 'paiements réussis' : 'paiement réussi' }}
                @if ($stats['payments_failed'] > 0)
                    · <span class="font-semibold text-red-200">{{ $stats['payments_failed'] }} {{ $stats['payments_failed'] > 1 ? 'échoués' : 'échoué' }}</span>
                @endif
            </p>
        </div>
        <div>
            <div class="flex items-baseline justify-between gap-3">
                <p class="text-sm font-medium text-white/75">Remplissage de la salle</p>
                <p class="font-display text-2xl font-extrabold">{{ $pct }} %</p>
            </div>
            <div class="mt-3 h-3 overflow-hidden rounded-full bg-white/15" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Remplissage de la salle">
                <div class="h-full rounded-full bg-safran transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <p class="mt-2 text-sm text-white/70">{{ $sold }} billets vendus sur {{ $total }}</p>
        </div>
    </div>

    {{-- Indicateurs --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @foreach ($kpis as [$label, $value, $icon, $tone])
            <div class="card p-4 sm:p-5">
                <span class="grid size-10 place-items-center rounded-xl {{ $tone }}"><x-ui.icon :name="$icon" class="size-5" /></span>
                <p class="mt-3 font-display text-3xl font-extrabold tabular-nums">{{ number_format($value, 0, ',', ' ') }}</p>
                <p class="mt-0.5 text-sm text-sourdine">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- Commandes récentes --}}
    <section class="card overflow-hidden">
        <div class="flex items-center justify-between gap-3 px-5 py-4">
            <h2 class="text-lg font-extrabold">Commandes récentes</h2>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm">Tout voir <x-ui.icon name="arrow-right" class="size-4" /></a>
        </div>

        @if ($recentOrders->isEmpty())
            <x-admin.empty icon="list" title="Aucune commande pour le moment" text="Les commandes apparaîtront ici dès la première réservation sur le site." />
        @else
            <div class="overflow-x-auto border-t border-bord">
                <table class="tbl">
                    <thead><tr><th>Référence</th><th>Client</th><th class="text-right">Montant</th><th>Statut</th><th>Date</th><th><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td class="font-semibold">{{ $order->reference }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td class="text-right tabular-nums">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</td>
                                <td><x-ui.status :value="$order->status" /></td>
                                <td class="whitespace-nowrap text-sourdine">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost btn-sm">Détails</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
