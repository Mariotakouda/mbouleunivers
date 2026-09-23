<x-layouts.admin title="Modifier la catégorie">
    <x-admin.header title="Modifier la catégorie" :subtitle="$ticketType->name"
        :back="route('admin.ticket-types.index')" backLabel="Liste des catégories" />

    <form method="POST" action="{{ route('admin.ticket-types.update', $ticketType) }}" class="card max-w-3xl p-5 sm:p-8" x-data="{ busy: false }" @submit="busy = true">
        @include('admin.ticket-types._form')
    </form>
</x-layouts.admin>
