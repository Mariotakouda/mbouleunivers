<x-layouts.admin title="Statistiques">
    <x-admin.header title="Statistiques" subtitle="Billets vendus, chiffre d'affaires et entrées scannées.">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-sm"><x-ui.icon name="list" class="size-4" /> Voir les commandes</a>
    </x-admin.header>

    @livewire('admin-dashboard')
</x-layouts.admin>
