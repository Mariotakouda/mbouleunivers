<x-layouts.admin title="Tableau de bord">
    <x-admin.header title="Tableau de bord" subtitle="Ventes et entrées du spectacle en un coup d'œil. Les chiffres se mettent à jour tout seuls.">
        <a href="{{ route('admin.events.create') }}" class="btn btn-ghost btn-sm"><x-ui.icon name="plus" class="size-4" /> Nouveau spectacle</a>
        <a href="{{ route('admin.ticket-types.create') }}" class="btn btn-primary btn-sm"><x-ui.icon name="plus" class="size-4" /> Nouvelle catégorie</a>
    </x-admin.header>

    @livewire('admin-dashboard')
</x-layouts.admin>
