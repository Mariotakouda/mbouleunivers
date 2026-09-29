<x-layouts.admin title="Commandes">
    <x-admin.header title="Commandes" subtitle="Chaque commande envoyée par un client arrive ici. Contactez-le sur WhatsApp, puis confirmez le paiement pour générer ses billets." />

    {{-- Onglets + recherche --}}
    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="flex flex-wrap gap-2" aria-label="Filtrer les commandes">
            @foreach ($tabs as $key => $label)
                @php $active = $tab === $key; @endphp
                <a href="{{ route('admin.orders.index', array_filter(['statut' => $key === 'all' ? null : $key, 'q' => $search ?: null])) }}"
                   @if ($active) aria-current="page" @endif
                   @class(['btn btn-sm', 'btn-dark' => $active, 'btn-ghost' => ! $active])>
                    {{ $label }}
                    @isset($counts[$key])
                        <span @class(['ml-1 rounded-full px-2 py-0.5 text-xs font-bold tabular-nums', 'bg-white/20' => $active, 'bg-craie' => ! $active, 'bg-alerte text-white' => ! $active && $key === 'pending' && $counts[$key] > 0])>{{ $counts[$key] }}</span>
                    @endisset
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex gap-2" role="search">
            @if ($tab !== 'all') <input type="hidden" name="statut" value="{{ $tab }}"> @endif
            <input type="search" name="q" value="{{ $search }}" placeholder="Nom, téléphone, référence…" class="input min-w-0 flex-1 lg:w-72" aria-label="Rechercher une commande">
            <button type="submit" class="btn btn-dark btn-sm">Rechercher</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        @if ($orders->isEmpty())
            <x-admin.empty icon="list"
                :title="$search || $tab !== 'all' ? 'Aucune commande trouvée' : 'Aucune commande'"
                :text="$search || $tab !== 'all' ? 'Essayez un autre filtre ou une autre recherche.' : 'Les commandes des clients apparaîtront ici.'" />
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Commande</th><th>Client</th><th>Billets</th>
                            <th class="text-right">Montant</th><th>Statut</th><th>WhatsApp</th><th>Reçue</th>
                            <th><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            @php $todo = $order->isAwaitingConfirmation(); @endphp
                            <tr @class(['bg-amber-50/50' => $todo])>
                                <td class="whitespace-nowrap font-semibold">{{ $order->reference }}</td>
                                <td>
                                    <p class="font-semibold">{{ $order->customer_name }}</p>
                                    <p class="text-sm text-sourdine">{{ $whatsApp->pretty($order->customer_phone) }}</p>
                                </td>
                                <td class="text-sm">
                                    @foreach ($order->items as $item)
                                        <p class="whitespace-nowrap">{{ $item->quantity }} × {{ $item->ticketType?->name ?? 'Billet' }}</p>
                                    @endforeach
                                </td>
                                <td class="whitespace-nowrap text-right tabular-nums">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    @if ($order->status === 'pending' && ! $todo)
                                        <x-ui.status value="expired" />
                                    @else
                                        <x-ui.status :value="$order->status" />
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-sm">
                                    @if ($order->whatsapp_clicked_at)
                                        <span class="inline-flex items-center gap-1 font-medium text-succes" title="{{ $order->whatsapp_clicked_at->format('d/m/Y H:i') }}">
                                            <x-ui.icon name="check" class="size-4" /> Envoyé
                                        </span>
                                    @elseif ($todo)
                                        <span class="text-sourdine">Pas encore</span>
                                    @else
                                        <span class="text-sourdine">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-sm text-sourdine">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="whitespace-nowrap text-right">
                                    @if ($url = $whatsApp->customerUrl($order))
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm text-succes" aria-label="Écrire à {{ $order->customer_name }} sur WhatsApp" title="Écrire sur WhatsApp">
                                            <x-ui.icon name="message-circle" class="size-4" />
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="eye" class="size-4" /> Détails</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</x-layouts.admin>
