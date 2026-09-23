@php
    $states = [
        'valid' => ['Billet valide', 'Entrée autorisée', 'check-circle', 'bg-succes text-white'],
        'already_used' => ['Billet déjà utilisé', 'Entrée refusée', 'x-circle', 'bg-erreur text-white'],
        'cancelled' => ['Billet annulé', 'Entrée refusée', 'x-circle', 'bg-erreur text-white'],
        'invalid' => ['Billet invalide', 'Entrée refusée : code inconnu ou commande non payée', 'x-circle', 'bg-erreur text-white'],
    ];
    $state = $states[$resultStatus] ?? null;
@endphp
<div class="space-y-5" x-data x-on:scan-done.window="$nextTick(() => $refs.code?.focus())">
    <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-extrabold">Scanner un billet</h1>
        <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-semibold ring-1 ring-bord">
            <x-ui.icon name="check-circle" class="size-4 text-succes" /> {{ $validToday }} {{ $validToday > 1 ? 'entrées' : 'entrée' }} aujourd'hui
        </p>
    </div>

    <form wire:submit="verify" class="card space-y-3 p-4 sm:p-5">
        <label for="scannedCode" class="label">Code du billet</label>
        <input id="scannedCode" x-ref="code" type="text" wire:model="scannedCode" autofocus autocomplete="off" autocapitalize="off" spellcheck="false"
               placeholder="Scannez le QR code du billet" class="input min-h-14 text-lg">
        <p class="hint">Le lecteur de QR code (câble ou Bluetooth) remplit ce champ tout seul, puis valide.</p>
        <button type="submit" class="btn btn-primary btn-lg w-full" wire:loading.attr="disabled" wire:target="verify">
            <span wire:loading.remove wire:target="verify" class="inline-flex items-center gap-2"><x-ui.icon name="scan" class="size-6" /> Vérifier le billet</span>
            <span wire:loading wire:target="verify" class="inline-flex items-center gap-2"><x-ui.icon name="loader" class="size-6 animate-spin" /> Vérification…</span>
        </button>
    </form>

    @if ($state)
        <div class="overflow-hidden rounded-2xl shadow-lg" role="alert" wire:key="result-{{ $resultStatus }}-{{ $ticketInfo['ticket_number'] ?? 'none' }}">
            <div class="flex items-center gap-4 px-5 py-6 {{ $state[3] }}">
                <x-ui.icon :name="$state[2]" class="size-14 shrink-0" />
                <div>
                    <p class="font-display text-2xl font-extrabold leading-tight">{{ $state[0] }}</p>
                    <p class="mt-1 font-semibold text-white/90">{{ $state[1] }}</p>
                </div>
            </div>
            @if ($ticketInfo)
                <dl class="grid gap-3 bg-white p-5 text-sm sm:grid-cols-3">
                    <div><dt class="text-sourdine">Spectacle</dt><dd class="font-semibold">{{ $ticketInfo['event'] }}</dd></div>
                    <div><dt class="text-sourdine">Catégorie</dt><dd class="font-semibold">{{ $ticketInfo['category'] }}</dd></div>
                    <div><dt class="text-sourdine">Billet</dt><dd class="font-semibold">{{ $ticketInfo['ticket_number'] }}</dd></div>
                </dl>
            @endif
            <div class="border-t border-bord bg-white p-4">
                <button type="button" wire:click="resetScan" class="btn btn-dark w-full"><x-ui.icon name="refresh" class="size-5" /> Scanner le billet suivant</button>
            </div>
        </div>
    @endif
</div>
