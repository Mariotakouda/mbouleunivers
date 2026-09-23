@props(['title' => null])
@php
    $nav = [
        ['Pilotage', [
            ['admin.dashboard', 'Tableau de bord', 'layout', 'admin.dashboard'],
            ['admin.statistics.index', 'Statistiques', 'chart', 'admin.statistics.*'],
        ]],
        ['Billetterie', [
            ['admin.events.index', 'Spectacle', 'calendar', 'admin.events.*'],
            ['admin.ticket-types.index', 'Catégories de billets', 'ticket', 'admin.ticket-types.*'],
        ]],
        ['Ventes', [
            ['admin.orders.index', 'Commandes', 'list', 'admin.orders.*'],
            ['admin.payments.index', 'Paiements', 'credit-card', 'admin.payments.*'],
            ['admin.tickets.index', 'Billets émis', 'qr', 'admin.tickets.*'],
        ]],
        ['Équipe', [
            ['admin.users.index', 'Utilisateurs', 'users', 'admin.users.*'],
        ]],
    ];
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#221a4c">
    <title>{{ $title ? "{$title} — " : '' }}Administration — Univers 2 M'boulè</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23221a4c'/%3E%3Ccircle cx='16' cy='16' r='7' fill='%23f6a21e'/%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen" x-data="{ menu: false }" @keydown.escape.window="menu = false">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-[80] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:font-semibold">Aller au contenu</a>

    {{-- Barre du haut (mobile / tablette) --}}
    <header class="sticky top-0 z-40 flex h-14 items-center gap-3 border-b border-white/10 bg-nuit px-3 text-white lg:hidden">
        <button type="button" @click="menu = true" class="grid size-11 place-items-center rounded-lg hover:bg-white/10" aria-label="Ouvrir le menu">
            <x-ui.icon name="menu" class="size-6" />
        </button>
        <p class="min-w-0 flex-1 truncate font-display text-base font-extrabold">{{ $title ?? 'Administration' }}</p>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="grid size-11 place-items-center rounded-lg hover:bg-white/10" aria-label="Voir le site public">
            <x-ui.icon name="external" class="size-5" />
        </a>
    </header>

    {{-- Fond sombre du menu mobile --}}
    <div x-show="menu" x-cloak x-transition.opacity @click="menu = false" class="fixed inset-0 z-50 bg-minuit/60 lg:hidden"></div>

    {{-- Menu latéral --}}
    <aside class="stage fixed inset-y-0 left-0 z-[60] flex w-72 max-w-[85vw] -translate-x-full flex-col text-white transition-transform duration-200 lg:translate-x-0"
           :class="menu && '!translate-x-0'" aria-label="Menu d'administration">
        <div class="flex h-16 shrink-0 items-center justify-between px-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 font-display text-lg font-extrabold">
                <span class="grid size-8 place-items-center rounded-lg bg-safran text-nuit"><x-ui.icon name="mic" class="size-4" /></span>
                Administration
            </a>
            <button type="button" @click="menu = false" class="grid size-10 place-items-center rounded-lg hover:bg-white/10 lg:hidden" aria-label="Fermer le menu">
                <x-ui.icon name="x" class="size-5" />
            </button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 pb-6 pt-2">
            @foreach ($nav as [$group, $items])
                <div>
                    <p class="px-3 pb-1.5 text-xs font-semibold text-white/50">{{ $group }}</p>
                    <ul class="space-y-0.5">
                        @foreach ($items as [$route, $label, $icon, $pattern])
                            @php $active = request()->routeIs($pattern); @endphp
                            <li>
                                <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                                   @class([
                                       'flex min-h-11 items-center gap-3 rounded-xl px-3 text-[0.95rem] font-medium transition-colors',
                                       'bg-safran text-nuit font-semibold' => $active,
                                       'text-white/85 hover:bg-white/10' => ! $active,
                                   ])>
                                    <x-ui.icon :name="$icon" class="size-5 shrink-0" /> {{ $label }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="shrink-0 space-y-2 border-t border-white/10 p-3">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-[0.95rem] font-medium text-white/85 hover:bg-white/10">
                <x-ui.icon name="external" class="size-5" /> Voir le site public
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-left text-[0.95rem] font-medium text-white/85 hover:bg-white/10">
                    <x-ui.icon name="log-out" class="size-5" />
                    <span class="min-w-0">
                        Se déconnecter
                        @if ($user) <span class="block truncate text-xs font-normal text-white/55">{{ $user->name }}</span> @endif
                    </span>
                </button>
            </form>
        </div>
    </aside>

    <main id="contenu" class="lg:pl-72">
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:py-10">
            @if (session('success'))
                <x-ui.alert type="success" class="mb-6">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
                <x-ui.alert type="error" class="mb-6">{{ session('error') }}</x-ui.alert>
            @endif
            @if ($errors->any() && ! request()->routeIs('admin.login'))
                <x-ui.alert type="error" class="mb-6">
                    <p class="font-semibold">Le formulaire contient des erreurs.</p>
                    <p class="mt-0.5">Vérifiez les champs signalés en rouge, puis enregistrez à nouveau.</p>
                </x-ui.alert>
            @endif

            {{ $slot }}
        </div>
    </main>

    @livewireScripts
</body>
</html>
