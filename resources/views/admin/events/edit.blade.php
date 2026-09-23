<x-layouts.admin title="Modifier le spectacle">
    <x-admin.header title="Modifier le spectacle" :subtitle="$event->title"
        :back="route('admin.events.show', $event)" backLabel="Fiche du spectacle" />

    <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="card max-w-3xl p-5 sm:p-8" x-data="{ busy: false }" @submit="busy = true">
        @include('admin.events._form')
    </form>
</x-layouts.admin>
