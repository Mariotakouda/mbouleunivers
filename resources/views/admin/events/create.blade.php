<x-layouts.admin title="Nouveau spectacle">
    <x-admin.header title="Nouveau spectacle" subtitle="Ces informations s'affichent sur la page d'accueil et sur les billets."
        :back="route('admin.events.index')" backLabel="Liste des spectacles" />

    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" class="card max-w-3xl p-5 sm:p-8" x-data="{ busy: false }" @submit="busy = true">
        @include('admin.events._form')
    </form>
</x-layouts.admin>
