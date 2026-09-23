@props(['title', 'subtitle' => null, 'back' => null, 'backLabel' => 'Retour'])
<div class="mb-6 sm:mb-8">
    @if ($back)
        <x-ui.back :href="$back" :label="$backLabel" class="mb-4" />
    @endif
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold sm:text-3xl">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1.5 max-w-2xl text-sourdine">{{ $subtitle }}</p>
            @endif
        </div>
        @if (! $slot->isEmpty())
            <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
        @endif
    </div>
</div>
