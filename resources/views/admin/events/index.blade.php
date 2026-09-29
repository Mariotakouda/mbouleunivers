<x-layouts.admin title="Spectacle">
    <x-admin.header title="Spectacle" subtitle="Créez le spectacle, publiez-le, puis ajoutez ses catégories de billets.">
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary"><x-ui.icon name="plus" class="size-5" /> Nouveau spectacle</a>
    </x-admin.header>

    <div class="card overflow-hidden">
        @if ($events->isEmpty())
            <x-admin.empty icon="calendar" title="Aucun spectacle créé" text="Commencez par créer votre spectacle : titre, date, lieu et affiche.">
                <a href="{{ route('admin.events.create') }}" class="btn btn-primary"><x-ui.icon name="plus" class="size-5" /> Créer le spectacle</a>
            </x-admin.empty>
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Spectacle</th><th>Date</th><th>Lieu</th><th>Statut</th><th><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.events.show', $event) }}" class="flex items-center gap-3 font-semibold hover:underline">
                                        @if ($event->hasPoster())
                                            <img src="{{ $event->posterUrl() }}" alt="" class="size-12 shrink-0 rounded-lg object-cover ring-1 ring-bord">
                                        @else
                                            <span class="grid size-12 shrink-0 place-items-center rounded-lg bg-craie text-sourdine ring-1 ring-bord"><x-ui.icon name="mic" class="size-5" /></span>
                                        @endif
                                        {{ $event->title }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap">{{ $event->date->format('d/m/Y') }}</td>
                                <td>{{ $event->venue }}</td>
                                <td><x-ui.status :value="$event->status" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.events.show', $event) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="eye" class="size-4" /> Voir</a>
                                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="edit" class="size-4" /> Modifier</a>
                                        <x-admin.confirm :action="route('admin.events.destroy', $event)" icon-only
                                            title="Supprimer « {{ $event->title }} » ?"
                                            message="Le spectacle et toutes ses catégories de billets seront supprimés. Cette action est définitive." />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $events->links() }}</div>
</x-layouts.admin>
