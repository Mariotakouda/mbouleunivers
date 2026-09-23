<x-layouts.admin title="Commandes">
    <x-admin.header title="Commandes" subtitle="Toutes les réservations faites sur le site, payées ou non." />

    <div class="card overflow-hidden">
        @if ($orders->isEmpty())
            <x-admin.empty icon="list" title="Aucune commande" text="Les commandes des clients apparaîtront ici." />
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Référence</th><th>Client</th><th class="text-right">Montant</th><th>Statut</th><th>Date</th><th><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="font-semibold">{{ $order->reference }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td class="whitespace-nowrap text-right tabular-nums">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</td>
                                <td><x-ui.status :value="$order->status" /></td>
                                <td class="whitespace-nowrap text-sourdine">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-right"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="eye" class="size-4" /> Détails</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</x-layouts.admin>
