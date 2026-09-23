<x-layouts.app title="Connexion administrateur" :back="route('home')" backLabel="Site public">
    <div class="mx-auto flex max-w-md flex-col px-4 py-10 md:py-16">
        <span class="grid size-14 place-items-center rounded-2xl bg-nuit text-safran">
            <x-ui.icon name="lock" class="size-7" />
        </span>
        <h1 class="mt-5 text-3xl font-extrabold">Espace administrateur</h1>
        <p class="mt-2 text-sourdine">Gérez le spectacle, les catégories de billets et les ventes.</p>

        @if ($errors->any())
            <x-ui.alert type="error" class="mt-6">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="card mt-6 space-y-5 p-5 sm:p-7"
              x-data="{ show: false, busy: false }" @submit="busy = true">
            @csrf
            <div>
                <label for="email" class="label">Adresse email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" inputmode="email"
                       autofocus required @class(['input', 'input-invalid' => $errors->has('email')])>
            </div>

            <div>
                <label for="password" class="label">Mot de passe</label>
                <div class="relative">
                    <input id="password" :type="show ? 'text' : 'password'" name="password" autocomplete="current-password" required class="input pr-14">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 grid w-12 place-items-center text-sourdine hover:text-nuit"
                            :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                        <x-ui.icon name="eye" class="size-5" x-show="!show" />
                        <x-ui.icon name="eye-off" class="size-5" x-show="show" x-cloak />
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="busy">
                <x-ui.icon name="loader" class="size-5 animate-spin" x-show="busy" x-cloak />
                <span x-text="busy ? 'Connexion…' : 'Se connecter'">Se connecter</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-sourdine">
            <a href="{{ route('agent.login') }}" class="font-semibold text-nuit underline decoration-safran decoration-2 underline-offset-4">Vous êtes agent de contrôle ? Connexion agent</a>
        </p>
    </div>
</x-layouts.app>
