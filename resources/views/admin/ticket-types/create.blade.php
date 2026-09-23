<x-layouts.admin title="Nouvelle catégorie">
    <x-admin.header title="Nouvelle catégorie de billets" subtitle="Définissez le nom, le prix et le nombre de places de cette catégorie."
        :back="route('admin.ticket-types.index')" backLabel="Liste des catégories" />

    @if ($events->isEmpty())
        <x-ui.alert type="warning" class="mb-6 max-w-3xl">
            Aucun spectacle n'existe encore. <a href="{{ route('admin.events.create') }}" class="font-semibold underline">Créez d'abord le spectacle</a>, puis revenez ici.
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.ticket-types.store') }}" class="card max-w-3xl p-5 sm:p-8" x-data="{ busy: false }" @submit="busy = true">
        @include('admin.ticket-types._form')
    </form>
</x-layouts.admin>
