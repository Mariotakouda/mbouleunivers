<x-layouts.admin title="Catégories de billets">
    <x-admin.header title="Catégories de billets" subtitle="Standard, VIP, Premium… Chaque catégorie a son prix et son nombre de places.">
        <a href="{{ route('admin.ticket-types.create') }}" class="btn btn-primary"><x-ui.icon name="plus" class="size-5" /> Nouvelle catégorie</a>
    </x-admin.header>

    <div class="card overflow-hidden">
        @if ($ticketTypes->isEmpty())
            <x-admin.empty icon="ticket" title="Aucune catégorie de billets" text="Créez au moins une catégorie pour ouvrir la vente.">
                <a href="{{ route('admin.ticket-types.create') }}" class="btn btn-primary"><x-ui.icon name="plus" class="size-5" /> Créer une catégorie</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Catégorie</th><th>Spectacle</th><th class="text-right">Prix</th><th>Places</th><th>Statut</th><th><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($ticketTypes as $type)
                            <tr>
                                <td class="font-semibold">{{ $type->name }}</td>
                                <td class="text-sourdine">{{ $type->event->title }}</td>
                                <td class="whitespace-nowrap text-right tabular-nums">{{ number_format($type->price, 0, ',', ' ') }} FCFA</td>
                                <td class="whitespace-nowrap tabular-nums">{{ $type->available_quantity }} <span class="text-sourdine">/ {{ $type->quantity }}</span></td>
                                <td><x-ui.status :value="$type->status" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.ticket-types.edit', $type) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="edit" class="size-4" /> Modifier</a>
                                        <x-admin.confirm :action="route('admin.ticket-types.destroy', $type)" icon-only
                                            title="Supprimer la catégorie « {{ $type->name }} » ?"
                                            message="Les clients ne pourront plus acheter cette catégorie. Si des billets ont déjà été vendus, préférez la passer en « Inactif »." />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $ticketTypes->links() }}</div>
</x-layouts.admin>
