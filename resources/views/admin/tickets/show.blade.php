<x-layouts.admin :title="'Billet '.$ticket->ticket_number">
    <x-admin.header :title="'Billet '.$ticket->ticket_number" :back="route('admin.tickets.index')" backLabel="Liste des billets">
        <x-ui.status :value="$ticket->status" class="!px-3 !py-1 !text-sm" />
    </x-admin.header>

    <section class="card max-w-2xl p-5 sm:p-6">
        <dl class="divide-y divide-bord text-sm">
            <div class="flex items-center justify-between gap-4 py-3 first:pt-0"><dt class="text-sourdine">Commande</dt><dd><a href="{{ route('admin.orders.show', $ticket->order) }}" class="font-semibold underline decoration-safran decoration-2 underline-offset-4">{{ $ticket->order->reference }}</a></dd></div>
            <div class="flex items-center justify-between gap-4 py-3"><dt class="text-sourdine">Client</dt><dd class="font-semibold">{{ $ticket->order->customer_name }}</dd></div>
            <div class="flex items-center justify-between gap-4 py-3"><dt class="text-sourdine">Catégorie</dt><dd class="font-semibold">{{ $ticket->ticketType->name }}</dd></div>
            @if ($ticket->used_at)
                <div class="flex items-center justify-between gap-4 py-3"><dt class="text-sourdine">Utilisé le</dt><dd class="font-semibold">{{ $ticket->used_at->format('d/m/Y H:i') }}</dd></div>
            @endif
        </dl>

        @if ($ticket->status === 'valid')
            <div class="mt-6 border-t border-bord pt-5">
                <x-admin.confirm :action="route('admin.tickets.cancel', $ticket)" method="POST" icon="x-circle" label="Annuler ce billet"
                    title="Annuler le billet {{ $ticket->ticket_number }} ?"
                    message="Le billet ne pourra plus être utilisé à l'entrée. Cette action est définitive."
                    confirm="Oui, annuler le billet" class="!border-erreur/30" />
            </div>
        @endif
    </section>

    <section class="mt-8">
        <h2 class="mb-4 text-xl font-extrabold">Historique des scans</h2>
        <div class="card overflow-hidden">
            @if ($ticket->scans->isEmpty())
                <x-admin.empty icon="scan" title="Jamais scanné" text="Ce billet n'a pas encore été présenté à l'entrée." />
            @else
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>Agent</th><th>Résultat</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach ($ticket->scans as $scan)
                                <tr>
                                    <td class="font-semibold">{{ $scan->user->name }}</td>
                                    <td><x-ui.status :value="$scan->result" /></td>
                                    <td class="whitespace-nowrap text-sourdine">{{ $scan->scanned_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</x-layouts.admin>
