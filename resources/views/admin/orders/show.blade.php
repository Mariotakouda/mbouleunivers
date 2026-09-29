@php $awaiting = $order->isAwaitingConfirmation(); @endphp
<x-layouts.admin :title="'Commande '.$order->reference">
    <x-admin.header :title="'Commande '.$order->reference" :back="route('admin.orders.index')" backLabel="Liste des commandes">
        @if ($order->status === 'pending' && ! $awaiting)
            <x-ui.status value="expired" class="!px-3 !py-1 !text-sm" />
        @else
            <x-ui.status :value="$order->status" class="!px-3 !py-1 !text-sm" />
        @endif
    </x-admin.header>

    {{-- ============ Actions selon l'état ============ --}}
    @if ($awaiting)
        <section class="card mb-6 border-alerte/30 bg-amber-50/60 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="max-w-xl">
                    <h2 class="text-lg font-extrabold">Commande à traiter</h2>
                    <p class="mt-1 text-sm text-sourdine">
                        1. Écrivez au client sur WhatsApp pour lui indiquer comment payer.
                        2. Une fois l'argent reçu (Flooz, T-Money, espèces), confirmez le paiement : ses billets sont générés.
                    </p>
                    <p class="mt-2 text-sm text-sourdine">
                        Places gardées jusqu'au <strong class="text-nuit">{{ $order->expires_at->format('d/m/Y à H:i') }}</strong>.
                        @if ($order->whatsapp_clicked_at)
                            Le client a ouvert WhatsApp le {{ $order->whatsapp_clicked_at->format('d/m à H:i') }}.
                        @else
                            Le client n'a pas encore ouvert WhatsApp : relancez-le si besoin.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($url = $whatsApp->customerUrl($order))
                        <a href="{{ $url }}" target="_blank" rel="noopener" style="background-color:#25D366;color:#fff;border-color:#25D366" class="btn btn-sm bg-[#25D366] text-white hover:brightness-95">
                            <x-ui.icon name="message-circle" class="size-4" /> Écrire au client
                        </a>
                    @endif

                    <x-admin.confirm :action="route('admin.orders.confirm-manual', $order)" method="POST" icon="check-circle" label="Confirmer le paiement" tone="success"
                        class="!bg-succes !text-white hover:!brightness-95"
                        title="Confirmer ce paiement ?"
                        message="Les billets de {{ $order->customer_name }} seront générés. Ne confirmez que si vous avez bien reçu {{ number_format($order->total_amount, 0, ',', ' ') }} FCFA."
                        confirm="Oui, j'ai reçu le paiement">
                        <x-slot:fields>
                            <div>
                                <label for="method" class="label">Mode de paiement</label>
                                <select id="method" name="method" class="input" required>
                                    @foreach ($paymentMethods as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="reference" class="label">Référence de transaction <span class="font-normal text-sourdine">(facultatif)</span></label>
                                <input id="reference" name="reference" type="text" maxlength="100" class="input" placeholder="Ex. code du SMS de confirmation">
                            </div>
                        </x-slot:fields>
                    </x-admin.confirm>

                    <form method="POST" action="{{ route('admin.orders.extend', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm"><x-ui.icon name="clock" class="size-4" /> Prolonger de {{ config('ticketing.hold_hours', 24) }} h</button>
                    </form>

                    <x-admin.confirm :action="route('admin.orders.cancel', $order)" method="POST" icon="x-circle" label="Annuler" tone="danger"
                        title="Annuler cette commande ?"
                        message="Les places retournent en vente. Le client verra que sa commande est annulée."
                        confirm="Oui, annuler la commande" />
                </div>
            </div>
        </section>
    @elseif ($order->status === 'paid')
        <section class="card mb-6 border-succes/30 bg-emerald-50/60 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-extrabold">Paiement confirmé</h2>
                    <p class="mt-1 text-sm text-sourdine">
                        {{ $order->customer_email ? "Les billets ont aussi été envoyés par email à {$order->customer_email}." : 'Le client n\'a pas d\'email : envoyez-lui ses billets par WhatsApp.' }}
                    </p>
                </div>
                @if ($url = $whatsApp->ticketsUrl($order))
                    <a href="{{ $url }}" target="_blank" rel="noopener" style="background-color:#25D366;color:#fff;border-color:#25D366" class="btn btn-sm bg-[#25D366] text-white hover:brightness-95">
                        <x-ui.icon name="message-circle" class="size-4" /> Envoyer les billets sur WhatsApp
                    </a>
                @endif
            </div>
        </section>
    @else
        <x-ui.alert type="warning" class="mb-6">
            {{ $order->status === 'cancelled' ? 'Cette commande a été annulée' . ($order->cancelled_at ? ' le ' . $order->cancelled_at->format('d/m/Y à H:i') : '') . '.' : 'La réservation a expiré.' }}
            Les places ont été remises en vente : si le client veut toujours ses billets, il doit refaire une commande.
        </x-ui.alert>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <section class="card p-5 sm:p-6">
            <h2 class="text-lg font-extrabold">Client</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex items-center gap-3"><x-ui.icon name="user" class="size-5 shrink-0 text-sourdine" /><div><dt class="text-sourdine">Nom</dt><dd class="font-semibold">{{ $order->customer_name }}</dd></div></div>
                <div class="flex items-center gap-3"><x-ui.icon name="phone" class="size-5 shrink-0 text-sourdine" /><div><dt class="text-sourdine">WhatsApp / téléphone</dt><dd class="font-semibold"><a href="tel:{{ $order->customer_phone }}" class="hover:underline">{{ $whatsApp->pretty($order->customer_phone) }}</a></dd></div></div>
                <div class="flex items-center gap-3"><x-ui.icon name="mail" class="size-5 shrink-0 text-sourdine" /><div><dt class="text-sourdine">Email</dt><dd class="break-all font-semibold">@if ($order->customer_email)<a href="mailto:{{ $order->customer_email }}" class="hover:underline">{{ $order->customer_email }}</a>@else<span class="font-normal text-sourdine">Non renseigné</span>@endif</dd></div></div>
                @if ($order->customer_note)
                    <div class="flex items-start gap-3"><x-ui.icon name="message" class="mt-0.5 size-5 shrink-0 text-sourdine" /><div><dt class="text-sourdine">Message du client</dt><dd class="whitespace-pre-line font-semibold">{{ $order->customer_note }}</dd></div></div>
                @endif
            </dl>
        </section>

        <section class="card p-5 sm:p-6">
            <h2 class="text-lg font-extrabold">Commande</h2>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex items-start justify-between gap-3">
                        <span><strong>{{ $item->quantity }} ×</strong> {{ $item->ticketType?->name ?? 'Billet' }}</span>
                        <span class="whitespace-nowrap font-semibold tabular-nums">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-4 space-y-3 border-t border-bord pt-4 text-sm">
                <div class="flex items-baseline justify-between gap-3"><dt class="text-sourdine">Total</dt><dd class="font-display text-2xl font-extrabold tabular-nums">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-sourdine">Reçue le</dt><dd class="font-semibold">{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
                @if ($order->paid_at)
                    <div class="flex justify-between gap-3"><dt class="text-sourdine">Payée le</dt><dd class="font-semibold">{{ $order->paid_at->format('d/m/Y H:i') }}</dd></div>
                @endif
            </dl>
            <a href="{{ route('order.show', $order->reference) }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm mt-5"><x-ui.icon name="external" class="size-4" /> Voir la page de suivi du client</a>
        </section>
    </div>

    <section class="mt-8">
        <h2 class="mb-4 text-xl font-extrabold">Billets</h2>
        <div class="card overflow-hidden">
            @if ($order->tickets->isEmpty())
                <x-admin.empty icon="ticket" title="Aucun billet émis" text="Les billets sont créés dès que vous confirmez le paiement." />
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
        <h2 class="mb-4 text-xl font-extrabold">Encaissements</h2>
        <div class="card overflow-hidden">
            @if ($order->payments->isEmpty())
                <x-admin.empty icon="credit-card" title="Aucun encaissement" text="Le paiement sera enregistré ici quand vous le confirmerez." />
            @else
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>Référence</th><th class="text-right">Montant</th><th>Mode</th><th>Statut</th><th><span class="sr-only">Actions</span></th></tr></thead>
                        <tbody>
                            @foreach ($order->payments as $payment)
                                <tr>
                                    <td class="font-semibold">{{ $payment->transaction_id ?? '—' }}</td>
                                    <td class="whitespace-nowrap text-right tabular-nums">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $payment->methodLabel() }}</td>
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
