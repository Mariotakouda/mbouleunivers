<x-layouts.admin :title="'Encaissement '.($payment->transaction_id ?? $payment->order->reference)">
    <x-admin.header :title="'Encaissement '.($payment->transaction_id ?? $payment->order->reference)" :back="route('admin.payments.index')" backLabel="Liste des encaissements">
        <x-ui.status :value="$payment->status" class="!px-3 !py-1 !text-sm" />
    </x-admin.header>

    <section class="card max-w-2xl p-5 sm:p-6">
        <dl class="divide-y divide-bord text-sm">
            <div class="flex items-center justify-between gap-4 py-3 first:pt-0"><dt class="text-sourdine">Montant</dt><dd class="font-display text-2xl font-extrabold tabular-nums">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</dd></div>
            <div class="flex items-center justify-between gap-4 py-3"><dt class="text-sourdine">Commande</dt><dd><a href="{{ route('admin.orders.show', $payment->order) }}" class="font-semibold underline decoration-safran decoration-2 underline-offset-4">{{ $payment->order->reference }}</a></dd></div>
            <div class="flex items-center justify-between gap-4 py-3"><dt class="text-sourdine">Mode de paiement</dt><dd class="font-semibold">{{ $payment->methodLabel() }}</dd></div>
            @if ($payment->paid_at)
                <div class="flex items-center justify-between gap-4 py-3 last:pb-0"><dt class="text-sourdine">Encaissé le</dt><dd class="font-semibold">{{ $payment->paid_at->format('d/m/Y H:i') }}</dd></div>
            @endif
        </dl>
    </section>
</x-layouts.admin>
