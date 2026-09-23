<x-layouts.admin title="Nouvel utilisateur">
    <x-admin.header title="Nouvel utilisateur" subtitle="Créez un compte administrateur ou agent de contrôle."
        :back="route('admin.users.index')" backLabel="Liste des utilisateurs" />

    <form method="POST" action="{{ route('admin.users.store') }}" class="card max-w-2xl p-5 sm:p-8" x-data="{ busy: false }" @submit="busy = true">
        @include('admin.users._form')
    </form>
</x-layouts.admin>
