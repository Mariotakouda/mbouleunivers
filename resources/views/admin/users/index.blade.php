<x-layouts.admin title="Utilisateurs">
    <x-admin.header title="Utilisateurs" subtitle="Administrateurs et agents de contrôle qui scannent les billets à l'entrée.">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><x-ui.icon name="plus" class="size-5" /> Nouvel utilisateur</a>
    </x-admin.header>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th><span class="sr-only">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="font-semibold">{{ $user->name }} @if ($user->id === auth()->id()) <span class="ml-1 text-xs font-normal text-sourdine">(vous)</span> @endif</td>
                            <td class="text-sourdine">{{ $user->email }}</td>
                            <td><x-ui.status :value="$user->role" /></td>
                            <td><x-ui.status :value="$user->status" /></td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="edit" class="size-4" /> Modifier</a>
                                    @if ($user->id !== auth()->id())
                                        <x-admin.confirm :action="route('admin.users.destroy', $user)" icon-only
                                            title="Supprimer {{ $user->name }} ?"
                                            message="Cette personne ne pourra plus se connecter. Cette action est définitive." />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
</x-layouts.admin>
