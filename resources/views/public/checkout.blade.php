<x-layouts.app title="Finaliser la commande" :back="route('events.show', $event)" backLabel="Modifier mes billets">
    <div class="border-b border-bord bg-white">
        <div class="mx-auto max-w-6xl px-4 py-4">
            <x-ui.stepper :current="2" />
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8 md:py-12">
        @livewire('checkout-form', ['event' => $event])
    </div>
</x-layouts.app>
