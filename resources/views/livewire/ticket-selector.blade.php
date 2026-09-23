<div
    x-data="{
        q: @js($config['q']),
        types: @js($config['types']),
        loading: false,
        get count() { return Object.values(this.q).reduce((s, n) => s + n, 0) },
        get total() { return Object.entries(this.q).reduce((s, [id, n]) => s + n * this.types[id].price, 0) },
        money(n) { return new Intl.NumberFormat('fr-FR').format(n).replace(/[\u202f\u00a0]/g, ' ') + ' FCFA' },
        inc(id) { if (this.q[id] < this.types[id].max) this.q[id]++ },
        dec(id) { if (this.q[id] > 0) this.q[id]-- },
        async go() {
            if (this.count === 0 || this.loading) return;
            this.loading = true;
            try { await $wire.proceedToCheckout(this.q) } finally { this.loading = false }
        },
    }"
>
    <div class="card p-5 sm:p-6">
        <h2 class="text-xl font-extrabold">Choisissez vos billets</h2>
        <p class="hint mt-1">Jusqu'à {{ $maxPerType }} billets par catégorie.</p>

        @error('selection')
            <x-ui.alert type="error" class="mt-4">{{ $message }}</x-ui.alert>
        @enderror

        @if ($types->isEmpty())
            <x-ui.alert type="info" class="mt-5">Aucune catégorie de billets n'est ouverte à la vente pour le moment.</x-ui.alert>
        @endif

        <div class="mt-5 space-y-3">
            @foreach ($types as $type)
                @php
                    $avail = (int) $type->available_quantity;
                    $low = $avail > 0 && $avail <= max(5, (int) ceil($type->quantity * 0.1));
                @endphp
                <div wire:key="type-{{ $type->id }}"
                     class="rounded-2xl border p-4 transition-colors"
                     :class="q[{{ $type->id }}] > 0 ? 'border-nuit bg-nuit/[0.04] ring-1 ring-nuit' : 'border-bord bg-white'">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-lg font-bold leading-tight">{{ $type->name }}</p>
                            @if ($type->description)
                                <p class="mt-0.5 text-sm text-sourdine">{{ $type->description }}</p>
                            @endif
                            <p class="mt-1.5 font-display text-xl font-extrabold">
                                {{ number_format($type->price, 0, ',', ' ') }} <span class="text-sm font-semibold text-sourdine">FCFA</span>
                            </p>
                            @if ($avail <= 0)
                                <p class="mt-1 text-sm font-semibold text-erreur">Complet</p>
                            @elseif ($low)
                                <p class="mt-1 text-sm font-semibold text-alerte">Plus que {{ $avail }} places</p>
                            @endif
                        </div>

                        @if ($avail > 0)
                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" @click="dec({{ $type->id }})" :disabled="q[{{ $type->id }}] <= 0"
                                        aria-label="Retirer un billet {{ $type->name }}"
                                        class="grid size-11 place-items-center rounded-full border border-bord bg-white text-nuit transition-colors hover:bg-craie disabled:cursor-not-allowed disabled:opacity-35">
                                    <x-ui.icon name="minus" class="size-5" />
                                </button>
                                <output class="w-9 text-center font-display text-xl font-extrabold tabular-nums" x-text="q[{{ $type->id }}]" aria-live="polite">0</output>
                                <button type="button" @click="inc({{ $type->id }})" :disabled="q[{{ $type->id }}] >= types[{{ $type->id }}].max"
                                        aria-label="Ajouter un billet {{ $type->name }}"
                                        class="grid size-11 place-items-center rounded-full bg-nuit text-white transition-colors hover:bg-minuit disabled:cursor-not-allowed disabled:opacity-35">
                                    <x-ui.icon name="plus" class="size-5" />
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Récapitulatif (ordinateur / tablette) --}}
        <div class="mt-6 hidden border-t border-bord pt-5 md:block">
            <div class="flex items-baseline justify-between">
                <span class="text-sourdine" x-text="count === 0 ? 'Aucun billet sélectionné' : count + (count > 1 ? ' billets' : ' billet')"></span>
                <span class="font-display text-2xl font-extrabold" x-text="money(total)">0 FCFA</span>
            </div>
            <button type="button" @click="go()" :disabled="count === 0 || loading" class="btn btn-primary btn-lg mt-4 w-full">
                <x-ui.icon name="loader" class="size-5 animate-spin" x-show="loading" x-cloak />
                <span x-text="loading ? 'Un instant…' : 'Continuer'">Continuer</span>
            </button>
            <p class="hint mt-3 flex items-center justify-center gap-1.5 text-center" x-show="count === 0">
                Ajoutez au moins un billet avec le bouton +
            </p>
        </div>
    </div>

    {{-- Barre collante (mobile) --}}
    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-bord bg-white/95 px-4 pt-3 backdrop-blur md:hidden"
         style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
        <div class="mx-auto flex max-w-xl items-center gap-4">
            <div class="min-w-0 leading-tight">
                <p class="text-xs text-sourdine" x-text="count === 0 ? 'Aucun billet' : count + (count > 1 ? ' billets' : ' billet')"></p>
                <p class="font-display text-lg font-extrabold" x-text="money(total)">0 FCFA</p>
            </div>
            <button type="button" @click="go()" :disabled="count === 0 || loading" class="btn btn-primary ml-auto flex-1 sm:max-w-xs">
                <x-ui.icon name="loader" class="size-5 animate-spin" x-show="loading" x-cloak />
                <span x-text="loading ? 'Un instant…' : 'Continuer'">Continuer</span>
            </button>
        </div>
    </div>
</div>
