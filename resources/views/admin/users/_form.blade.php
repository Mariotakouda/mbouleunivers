@csrf
@php($user = $user ?? null)
@if ($user) @method('PUT') @endif

<div class="space-y-6">
    <div>
        <label for="name" class="label">Nom complet</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user?->name) }}" required autocomplete="off" @class(['input', 'input-invalid' => $errors->has('name')])>
        @error('name') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" required autocomplete="off" inputmode="email" @class(['input', 'input-invalid' => $errors->has('email')])>
            @error('email') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
        <div>
            <label for="phone" class="label">Téléphone <span class="font-normal text-sourdine">(facultatif)</span></label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone', $user?->phone) }}" inputmode="tel" placeholder="+228 90 12 34 56" @class(['input', 'input-invalid' => $errors->has('phone')])>
            @error('phone') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
    </div>

    <div x-data="{ show: false }">
        <label for="password" class="label">Mot de passe</label>
        <div class="relative sm:max-w-md">
            <input id="password" :type="show ? 'text' : 'password'" name="password" autocomplete="new-password" @if (! $user) required @endif class="input pr-14 {{ $errors->has('password') ? 'input-invalid' : '' }}">
            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 grid w-12 place-items-center text-sourdine hover:text-nuit"
                    :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                <x-ui.icon name="eye" class="size-5" x-show="!show" />
                <x-ui.icon name="eye-off" class="size-5" x-show="show" x-cloak />
            </button>
        </div>
        @if ($user) <p class="hint">Laissez vide pour conserver le mot de passe actuel.</p> @else <p class="hint">Choisissez un mot de passe que seule cette personne connaît.</p> @endif
        @error('password') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="role" class="label">Rôle</label>
            <select id="role" name="role" class="input">
                @foreach (['admin' => 'Administrateur (gère le site)', 'agent' => 'Agent (scanne les billets)'] as $value => $text)
                    <option value="{{ $value }}" @selected(old('role', $user?->role ?? 'agent') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="label">Compte</label>
            <select id="status" name="status" class="input">
                @foreach (['active' => 'Actif', 'inactive' => 'Désactivé'] as $value => $text)
                    <option value="{{ $value }}" @selected(old('status', $user?->status ?? 'active') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-bord pt-6 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary" :disabled="busy">
            <x-ui.icon name="loader" class="size-5 animate-spin" x-show="busy" x-cloak />
            <span x-text="busy ? 'Enregistrement…' : 'Enregistrer'">Enregistrer</span>
        </button>
    </div>
</div>
