@props(['href', 'label' => 'Retour', 'tone' => 'light'])
<a href="{{ $href }}"
   {{ $attributes->class([
        'group inline-flex min-h-11 items-center gap-1.5 rounded-full py-2 pl-2.5 pr-4 text-sm font-semibold transition-colors',
        'border border-bord bg-white text-nuit hover:bg-craie' => $tone === 'light',
        'border border-white/20 bg-white/10 text-white hover:bg-white/20' => $tone === 'dark',
    ]) }}>
    <x-ui.icon name="chevron-left" class="size-5 transition-transform group-hover:-translate-x-0.5" />
    <span>{{ $label }}</span>
</a>
