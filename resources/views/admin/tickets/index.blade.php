<x-layouts.admin title="Billets émis">
    <x-admin.header title="Billets émis" subtitle="Chaque billet payé, avec son QR code, son statut et son historique de scans." />

    <div class="card overflow-hidden">
        @if ($tickets->isEmpty())
            <x-admin.empty icon="qr" title="Aucun billet émis" text="Les billets sont créés automatiquement après chaque paiement confirmé." />
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>N° de billet</th><th>Commande</th><th>Catégorie</th><th>Statut</th><th><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td class="font-semibold">{{ $ticket->ticket_number }}</td>
                                <td>{{ $ticket->order->reference }}</td>
                                <td>{{ $ticket->ticketType->name }}</td>
                                <td><x-ui.status :value="$ticket->status" /></td>
                                <td class="text-right"><a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="eye" class="size-4" /> Détails</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $tickets->links() }}</div>
</x-layouts.admin>
