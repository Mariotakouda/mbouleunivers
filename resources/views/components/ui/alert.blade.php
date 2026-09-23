@props(['type' => 'info'])
@php
    $styles = [
        'success' => ['bg-emerald-50 border-succes/30 text-emerald-900', 'check-circle'],
        'error' => ['bg-red-50 border-erreur/30 text-red-900', 'alert'],
        'warning' => ['bg-amber-50 border-alerte/30 text-amber-900', 'alert'],
        'info' => ['bg-indigo-50 border-nuit/15 text-nuit', 'info'],
    ];
    [$tone, $icon] = $styles[$type] ?? $styles['info'];
@endphp
<div role="{{ $type === 'error' ? 'alert' : 'status' }}" {{ $attributes->class(['flex items-start gap-3 rounded-xl border px-4 py-3 text-sm', $tone]) }}>
    <x-ui.icon :name="$icon" class="mt-0.5 size-5 shrink-0" />
    <div class="min-w-0 flex-1">{{ $slot }}</div>
</div>
