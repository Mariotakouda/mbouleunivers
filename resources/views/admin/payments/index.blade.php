<x-layouts.admin title="Encaissements">
    <x-admin.header title="Encaissements" subtitle="Paiements que vous avez confirmés (Flooz, T-Money, espèces…)." />

    <div class="card overflow-hidden">
        @if ($payments->isEmpty())
            <x-admin.empty icon="credit-card" title="Aucun encaissement" text="Les paiements apparaîtront ici dès que vous en confirmerez un depuis une commande." />
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Référence</th><th>Commande</th><th>Mode</th><th class="text-right">Montant</th><th>Statut</th><th><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td class="font-semibold">{{ $payment->transaction_id ?? '—' }}</td>
                                <td>{{ $payment->order->reference }}</td>
                                <td>{{ $payment->methodLabel() }}</td>
                                <td class="whitespace-nowrap text-right tabular-nums">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                                <td><x-ui.status :value="$payment->status" /></td>
                                <td class="text-right"><a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="eye" class="size-4" /> Détails</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $payments->links() }}</div>
</x-layouts.admin>
