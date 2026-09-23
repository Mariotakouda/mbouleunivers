<x-layouts.admin :title="'Commande '.$order->reference">
    <x-admin.header :title="'Commande '.$order->reference" :back="route('admin.orders.index')" backLabel="Liste des commandes">
        <x-ui.status :value="$order->status" class="!px-3 !py-1 !text-sm" />
    </x-admin.header>

    @if ($order->status === 'pending')
        <x-ui.alert type="warning" class="mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-semibold">Paiement non confirmé</p>
                    <p class="mt-0.5">Si le client vous a réglé directement (Mobile Money via WhatsApp), confirmez ici pour générer ses billets.</p>
                </div>
                <x-admin.confirm :action="route('admin.orders.confirm-manual', $order)" method="POST" icon="check-circle" label="Confirmer le paiement" tone="success"
                    title="Confirmer ce paiement ?"
                    message="Les billets de {{ $order->customer_name }} seront générés et envoyés par email. Ne confirmez que si vous avez bien reçu l'argent."
                    confirm="Oui, j'ai reçu le paiement" />
            </div>
        </x-ui.alert>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <section class="card p-5 sm:p-6">
            <h2 class="text-lg font-extrabold">Client</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex items-center gap-3"><x-ui.icon name="user" class="size-5 shrink-0 text-sourdine" /><div><dt class="text-sourdine">Nom</dt><dd class="font-semibold">{{ $order->customer_name }}</dd></div></div>
                <div class="flex items-center gap-3"><x-ui.icon name="phone" class="size-5 shrink-0 text-sourdine" /><div><dt class="text-sourdine">Téléphone</dt><dd class="font-semibold"><a href="tel:{{ $order->customer_phone }}" class="hover:underline">{{ $order->customer_phone }}</a></dd></div></div>
                <div class="flex items-center gap-3"><x-ui.icon name="mail" class="size-5 shrink-0 text-sourdine" /><div><dt class="text-sourdine">Email</dt><dd class="break-all font-semibold"><a href="mailto:{{ $order->customer_email }}" class="hover:underline">{{ $order->customer_email }}</a></dd></div></div>
            </dl>
        </section>

        <section class="card p-5 sm:p-6">
            <h2 class="text-lg font-extrabold">Résumé</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex items-baseline justify-between gap-3"><dt class="text-sourdine">Total</dt><dd class="font-display text-2xl font-extrabold tabular-nums">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-sourdine">Créée le</dt><dd class="font-semibold">{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
                @if ($order->paid_at)
                    <div class="flex justify-between gap-3"><dt class="text-sourdine">Payée le</dt><dd class="font-semibold">{{ $order->paid_at->format('d/m/Y H:i') }}</dd></div>
                @endif
            </dl>
            @if ($order->status === 'paid')
                <a href="{{ route('ticket.show', $order->reference) }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm mt-5"><x-ui.icon name="external" class="size-4" /> Voir la page des billets du client</a>
            @endif
        </section>
    </div>

    <section class="mt-8">
        <h2 class="mb-4 text-xl font-extrabold">Billets</h2>
        <div class="card overflow-hidden">
            @if ($order->tickets->isEmpty())
                <x-admin.empty icon="ticket" title="Aucun billet émis" text="Les billets sont créés dès que le paiement est confirmé." />
            @else
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>N° de billet</th><th>Catégorie</th><th>Statut</th><th><span class="sr-only">Actions</span></th></tr></thead>
                        <tbody>
                            @foreach ($order->tickets as $ticket)
                                <tr>
                                    <td class="font-semibold">{{ $ticket->ticket_number }}</td>
                                    <td>{{ $ticket->ticketType->name }}</td>
                                    <td><x-ui.status :value="$ticket->status" /></td>
                                    <td class="text-right"><a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-ghost btn-sm">Détails</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>

    <section class="mt-8">
        <h2 class="mb-4 text-xl font-extrabold">Paiements</h2>
        <div class="card overflow-hidden">
            @if ($order->payments->isEmpty())
                <x-admin.empty icon="credit-card" title="Aucun paiement" text="Aucune tentative de paiement n'a été enregistrée pour cette commande." />
            @else
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>Transaction</th><th class="text-right">Montant</th><th>Méthode</th><th>Statut</th><th><span class="sr-only">Actions</span></th></tr></thead>
                        <tbody>
                            @foreach ($order->payments as $payment)
                                <tr>
                                    <td class="font-semibold">{{ $payment->transaction_id ?? '—' }}</td>
                                    <td class="whitespace-nowrap text-right tabular-nums">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $payment->method === 'manual_whatsapp' ? 'WhatsApp (manuel)' : ($payment->method ?? '—') }}</td>
                                    <td><x-ui.status :value="$payment->status" /></td>
                                    <td class="text-right"><a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-ghost btn-sm">Détails</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</x-layouts.admin>
