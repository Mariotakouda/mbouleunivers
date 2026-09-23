@props(['current' => 1])
@php $steps = ['Billets', 'Vos infos', 'Paiement']; @endphp
<ol class="flex items-center gap-2 text-sm" aria-label="Étapes de la commande">
    @foreach ($steps as $i => $label)
        @php $n = $i + 1; $done = $n < $current; $active = $n === $current; @endphp
        <li class="flex items-center gap-2" @if($active) aria-current="step" @endif>
            <span @class([
                'grid size-7 shrink-0 place-items-center rounded-full text-xs font-bold',
                'bg-succes text-white' => $done,
                'bg-nuit text-white' => $active,
                'border border-bord bg-white text-sourdine' => !$done && !$active,
            ])>
                @if ($done)
                    <x-ui.icon name="check" class="size-4" />
                @else
                    {{ $n }}
                @endif
            </span>
            <span @class([
                'font-semibold',
                'text-nuit' => $active || $done,
                'text-sourdine' => !$active && !$done,
                'hidden sm:inline' => !$active,
            ])>{{ $label }}</span>
        </li>
        @if (!$loop->last)
            <li aria-hidden="true" @class(['h-px w-6 sm:w-10', 'bg-succes' => $done, 'bg-bord' => !$done])></li>
        @endif
    @endforeach
</ol>
