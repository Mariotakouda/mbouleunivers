<x-layouts.admin title="Modifier l'utilisateur">
    <x-admin.header title="Modifier l'utilisateur" :subtitle="$user->name"
        :back="route('admin.users.index')" backLabel="Liste des utilisateurs" />

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card max-w-2xl p-5 sm:p-8" x-data="{ busy: false }" @submit="busy = true">
        @include('admin.users._form')
    </form>
</x-layouts.admin>
