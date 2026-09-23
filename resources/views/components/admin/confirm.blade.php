@props([
    'action',
    'method' => 'DELETE',
    'label' => 'Supprimer',
    'icon' => 'trash',
    'title' => 'Supprimer cet élément ?',
    'message' => 'Cette action est définitive.',
    'confirm' => 'Oui, supprimer',
    'tone' => 'danger',
    'iconOnly' => false,
])
<div x-data="{ open: false }" class="inline-block" @keydown.escape.window="open = false">
    <button type="button" @click="open = true"
            @if ($iconOnly) aria-label="{{ $label }}" title="{{ $label }}" @endif
            {{ $attributes->class([
                'btn btn-sm border border-transparent',
                'text-erreur hover:bg-red-50' => $tone === 'danger',
                'text-succes hover:bg-emerald-50' => $tone === 'success',
                'text-nuit hover:bg-craie' => ! in_array($tone, ['danger', 'success']),
            ]) }}>
        <x-ui.icon :name="$icon" class="size-4" />
        @unless ($iconOnly) <span>{{ $label }}</span> @endunless
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-[70] grid place-items-center p-4" role="dialog" aria-modal="true" aria-labelledby="dlg-{{ md5($action) }}">
        <div class="absolute inset-0 bg-minuit/60" @click="open = false" x-transition.opacity></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 text-left shadow-2xl" x-transition>
            <span @class([
                'grid size-12 place-items-center rounded-full',
                'bg-red-50 text-erreur' => $tone === 'danger',
                'bg-emerald-50 text-succes' => $tone === 'success',
                'bg-amber-50 text-alerte' => ! in_array($tone, ['danger', 'success']),
            ])>
                <x-ui.icon name="alert" class="size-6" />
            </span>
            <h2 id="dlg-{{ md5($action) }}" class="mt-4 text-xl font-extrabold">{{ $title }}</h2>
            <p class="mt-2 leading-relaxed text-sourdine">{{ $message }}</p>
            <form method="POST" action="{{ $action }}" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                @csrf
                @if (strtoupper($method) !== 'POST') @method($method) @endif
                <button type="button" @click="open = false" class="btn btn-ghost">Annuler</button>
                <button type="submit" @class([
                    'btn',
                    'btn-danger' => $tone === 'danger',
                    'btn-success' => $tone === 'success',
                    'btn-dark' => ! in_array($tone, ['danger', 'success']),
                ])>{{ $confirm }}</button>
            </form>
        </div>
    </div>
</div>
