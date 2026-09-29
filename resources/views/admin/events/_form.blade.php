@csrf
@php($event = $event ?? null)
@if ($event) @method('PUT') @endif

<div class="space-y-6">
    <div>
        <label for="title" class="label">Titre du spectacle</label>
        <input id="title" type="text" name="title" value="{{ old('title', $event?->title) }}" maxlength="150" required
               @class(['input', 'input-invalid' => $errors->has('title')])>
        @error('title') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="label">Description</label>
        <textarea id="description" name="description" rows="5" required @class(['input', 'input-invalid' => $errors->has('description')])>{{ old('description', $event?->description) }}</textarea>
        <p class="hint">Présentez le spectacle en quelques phrases : elle s'affiche sur la page d'accueil.</p>
        @error('description') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
    </div>

    <div x-data="{ preview: null }">
        <label for="image" class="label">Affiche @unless ($event) <span class="font-normal text-sourdine">(obligatoire)</span> @endunless</label>
        <div class="flex flex-wrap items-start gap-4">
            <div class="min-w-0 flex-1 basis-64">
                <input id="image" type="file" name="image" accept="image/*" @if (! $event) required @endif
                       @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null"
                       @class(['input file:mr-4 file:rounded-lg file:border-0 file:bg-nuit file:px-4 file:py-2 file:font-semibold file:text-white', 'input-invalid' => $errors->has('image')])>
                <p class="hint">Image JPG ou PNG, 4 Mo maximum.@if ($event?->hasPoster()) Laissez vide pour garder l'affiche actuelle.@endif</p>
                @error('image') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
            </div>
            @if ($event?->hasPoster())
                <img x-show="!preview" src="{{ $event->posterUrl() }}" alt="Affiche actuelle" class="h-28 rounded-xl ring-1 ring-bord">
            @endif
            <img x-show="preview" x-cloak :src="preview" alt="Aperçu de la nouvelle affiche" class="h-28 rounded-xl ring-1 ring-bord">
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="date" class="label">Date</label>
            <input id="date" type="date" name="date" value="{{ old('date', $event?->date?->format('Y-m-d')) }}" required
                   @class(['input', 'input-invalid' => $errors->has('date')])>
            @error('date') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
        <div>
            <label for="status" class="label">Visibilité sur le site</label>
            <select id="status" name="status" class="input">
                @foreach (['draft' => 'Brouillon (masqué)', 'published' => 'Publié (visible et en vente)', 'completed' => 'Terminé', 'cancelled' => 'Annulé'] as $value => $text)
                    <option value="{{ $value }}" @selected(old('status', $event?->status ?? 'draft') === $value)>{{ $text }}</option>
                @endforeach
            </select>
            <p class="hint">Choisissez « Publié » pour que le spectacle apparaisse sur le site.</p>
            @error('status') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="start_time" class="label">Heure de début</label>
            <input id="start_time" type="time" name="start_time" value="{{ old('start_time', $event?->start_time ? substr($event->start_time, 0, 5) : '') }}" required
                   @class(['input', 'input-invalid' => $errors->has('start_time')])>
            @error('start_time') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
        <div>
            <label for="end_time" class="label">Heure de fin <span class="font-normal text-sourdine">(facultatif)</span></label>
            <input id="end_time" type="time" name="end_time" value="{{ old('end_time', $event?->end_time ? substr($event->end_time, 0, 5) : '') }}"
                   @class(['input', 'input-invalid' => $errors->has('end_time')])>
            @error('end_time') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="venue" class="label">Salle</label>
            <input id="venue" type="text" name="venue" value="{{ old('venue', $event?->venue) }}" maxlength="150" required placeholder="Ex. Palais des Congrès"
                   @class(['input', 'input-invalid' => $errors->has('venue')])>
            @error('venue') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
        <div>
            <label for="address" class="label">Adresse <span class="font-normal text-sourdine">(facultatif)</span></label>
            <input id="address" type="text" name="address" value="{{ old('address', $event?->address) }}" maxlength="255" placeholder="Ex. Boulevard du 13 Janvier, Lomé"
                   @class(['input', 'input-invalid' => $errors->has('address')])>
            <p class="hint">Sert aussi pour le bouton « Itinéraire » du site.</p>
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-bord pt-6 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.events.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary" :disabled="busy">
            <x-ui.icon name="loader" class="size-5 animate-spin" x-show="busy" x-cloak />
            <span x-text="busy ? 'Enregistrement…' : 'Enregistrer le spectacle'">Enregistrer le spectacle</span>
        </button>
    </div>
</div>
