@csrf
@php($ticketType = $ticketType ?? null)
@if ($ticketType) @method('PUT') @endif

<div class="space-y-6">
    <div>
        <label for="event_id" class="label">Spectacle</label>
        <select id="event_id" name="event_id" required @class(['input', 'input-invalid' => $errors->has('event_id')])>
            @foreach ($events as $event)
                <option value="{{ $event->id }}" @selected((int) old('event_id', $ticketType?->event_id ?? ($selectedEventId ?? 0) ?: $events->first()?->id) === $event->id)>{{ $event->title }}</option>
            @endforeach
        </select>
        @error('event_id') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
    </div>

    <div>
        <label for="name" class="label">Nom de la catégorie</label>
        <input id="name" type="text" name="name" value="{{ old('name', $ticketType?->name) }}" maxlength="100" required placeholder="Ex. VIP"
               @class(['input', 'input-invalid' => $errors->has('name')])>
        @error('name') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="label">Description <span class="font-normal text-sourdine">(facultatif)</span></label>
        <textarea id="description" name="description" rows="3" placeholder="Ex. Places au premier rang, accès prioritaire"
                  @class(['input', 'input-invalid' => $errors->has('description')])>{{ old('description', $ticketType?->description) }}</textarea>
        <p class="hint">Visible par les clients sous le nom de la catégorie.</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="price" class="label">Prix (FCFA)</label>
            <input id="price" type="number" inputmode="numeric" min="0" step="1" name="price" value="{{ old('price', $ticketType ? (int) $ticketType->price : '') }}" required placeholder="5000"
                   @class(['input', 'input-invalid' => $errors->has('price')])>
            @error('price') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
        <div>
            <label for="quantity" class="label">Nombre de places</label>
            <input id="quantity" type="number" inputmode="numeric" min="1" step="1" name="quantity" value="{{ old('quantity', $ticketType?->quantity) }}" required placeholder="100"
                   @class(['input', 'input-invalid' => $errors->has('quantity')])>
            @error('quantity') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
    </div>

    @if ($ticketType)
        <div>
            <label for="available_quantity" class="label">Places encore disponibles</label>
            <input id="available_quantity" type="number" inputmode="numeric" min="0" step="1" name="available_quantity" value="{{ old('available_quantity', $ticketType->available_quantity) }}" required
                   @class(['input sm:max-w-xs', 'input-invalid' => $errors->has('available_quantity')])>
            <p class="hint">À ne modifier qu'en cas de correction : ce nombre baisse automatiquement à chaque vente.</p>
            @error('available_quantity') <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p> @enderror
        </div>
    @endif

    <div>
        <label for="status" class="label">Vente</label>
        <select id="status" name="status" class="input sm:max-w-xs">
            @foreach (['active' => 'Ouverte à la vente', 'inactive' => 'Fermée (masquée sur le site)'] as $value => $text)
                <option value="{{ $value }}" @selected(old('status', $ticketType?->status ?? 'active') === $value)>{{ $text }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-bord pt-6 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.ticket-types.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary" :disabled="busy">
            <x-ui.icon name="loader" class="size-5 animate-spin" x-show="busy" x-cloak />
            <span x-text="busy ? 'Enregistrement…' : 'Enregistrer la catégorie'">Enregistrer la catégorie</span>
        </button>
    </div>
</div>
