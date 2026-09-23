@props(['title' => "Univers 2 M'boulè", 'text' => null, 'tone' => 'light'])
<button type="button"
    x-data="{ copied: false }"
    @click="
        const data = { title: @js($title), text: @js($text ?? $title), url: window.location.href };
        if (navigator.share) {
            navigator.share(data).catch(() => {});
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(data.url).then(() => { copied = true; setTimeout(() => copied = false, 2200); });
        }
    "
    {{ $attributes->class([
        'btn',
        'btn-ghost' => $tone === 'light',
        'border border-white/25 bg-transparent text-white hover:bg-white/10' => $tone === 'dark',
    ]) }}>
    <x-ui.icon :name="'share'" class="size-5" />
    <span x-show="!copied">Partager</span>
    <span x-show="copied" x-cloak>Lien copié</span>
</button>
