<div class="grid gap-8 md:grid-cols-[1fr_22rem] md:items-start lg:grid-cols-[1fr_24rem]">
    {{-- Formulaire --}}
    <div class="md:order-1">
        <h1 class="text-3xl font-extrabold">Vos informations</h1>
        <p class="mt-2 text-sourdine">Nous vous répondons sur WhatsApp pour finaliser votre commande. Pas de compte à créer, aucun paiement en ligne.</p>

        @error('items')
            <x-ui.alert type="error" class="mt-5">{{ $message }}</x-ui.alert>
        @enderror

        <form wire:submit="submit" class="card mt-6 space-y-5 p-5 sm:p-7" novalidate>
            <div>
                <label for="customerName" class="label">Nom complet</label>
                <input id="customerName" type="text" wire:model.blur="customerName" autocomplete="name"
                       placeholder="Ex. Kofi Mensah" @class(['input', 'input-invalid' => $errors->has('customerName')])
                       @if ($errors->has('customerName')) aria-invalid="true" aria-describedby="err-name" @endif>
                @error('customerName')
                    <p id="err-name" class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="customerPhone" class="label">Numéro WhatsApp</label>
                <div class="relative">
                    <input id="customerPhone" type="tel" inputmode="tel" wire:model.blur="customerPhone" autocomplete="tel"
                           placeholder="90 12 34 56" @class(['input', 'input-invalid' => $errors->has('customerPhone')])
                           @if ($errors->has('customerPhone')) aria-invalid="true" aria-describedby="err-phone" @endif>
                </div>
                @error('customerPhone')
                    <p id="err-phone" class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p>
                @else
                    <p class="hint">8 chiffres, avec ou sans +228 (ou un numéro international avec +). C'est là que nous vous écrirons.</p>
                @enderror
            </div>

            <div>
                <label for="customerEmail" class="label">Email <span class="font-normal text-sourdine">(facultatif)</span></label>
                <input id="customerEmail" type="email" inputmode="email" wire:model.blur="customerEmail" autocomplete="email"
                       placeholder="vous@exemple.com" @class(['input', 'input-invalid' => $errors->has('customerEmail')])
                       @if ($errors->has('customerEmail')) aria-invalid="true" aria-describedby="err-email" @endif>
                @error('customerEmail')
                    <p id="err-email" class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p>
                @else
                    <p class="hint">Si vous en avez une, nous y envoyons aussi vos billets.</p>
                @enderror
            </div>

            <div>
                <label for="customerNote" class="label">Message <span class="font-normal text-sourdine">(facultatif)</span></label>
                <textarea id="customerNote" rows="3" wire:model.blur="customerNote" maxlength="500"
                          placeholder="Une question, une demande particulière…" @class(['input', 'input-invalid' => $errors->has('customerNote')])></textarea>
                @error('customerNote')
                    <p class="field-error"><x-ui.icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-full" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-2">
                    <x-ui.icon name="message-circle" class="size-5" /> Envoyer ma commande
                </span>
                <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                    <x-ui.icon name="loader" class="size-5 animate-spin" /> Enregistrement de la commande…
                </span>
            </button>

            <p class="flex items-start gap-2 text-sm text-sourdine">
                <x-ui.icon name="clock" class="mt-0.5 size-4 shrink-0" />
                Vos places sont réservées {{ $holdHours }} h, le temps de finaliser avec nous sur WhatsApp.
            </p>
        </form>
    </div>

    {{-- Récapitulatif --}}
    <aside class="md:order-2 md:sticky md:top-24">
        <div class="card overflow-hidden">
            <div class="stage px-5 py-4 text-white">
                <p class="font-display text-lg font-extrabold leading-tight">{{ $event->title }}</p>
                <p class="mt-1 text-sm text-white/75">{{ $event->dateLong() }} · {{ $event->startTimeLabel() }}</p>
                <p class="text-sm text-white/75">{{ $event->venue }}</p>
            </div>

            <div class="space-y-3 p-5">
                @foreach ($this->selectedTickets as $ticket)
                    <div class="flex items-start justify-between gap-3 text-sm">
                        <span><strong>{{ $ticket['quantity'] }} ×</strong> {{ $ticket['name'] }}</span>
                        <span class="font-semibold tabular-nums">{{ number_format($ticket['subtotal'], 0, ',', ' ') }} FCFA</span>
                    </div>
                @endforeach

                <div class="flex items-baseline justify-between border-t border-bord pt-4">
                    <span class="font-semibold">Total</span>
                    <span class="font-display text-2xl font-extrabold tabular-nums">{{ number_format($this->total, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        <p class="mt-4 flex items-center gap-2 text-sm text-sourdine">
            <x-ui.icon name="shield-check" class="size-5 shrink-0 text-succes" />
            Vous ne payez rien maintenant : après votre message WhatsApp, nous confirmons et vous réglez par Mobile Money.
        </p>
    </aside>
</div>
