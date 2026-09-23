@props(['icon' => 'list', 'title', 'text' => null])
<div class="flex flex-col items-center px-6 py-14 text-center">
    <span class="grid size-14 place-items-center rounded-2xl bg-craie text-sourdine ring-1 ring-bord">
        <x-ui.icon :name="$icon" class="size-7" />
    </span>
    <p class="mt-4 text-lg font-bold">{{ $title }}</p>
    @if ($text)
        <p class="mt-1 max-w-sm text-sourdine">{{ $text }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
