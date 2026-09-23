@props(['title' => null, 'back' => null, 'backLabel' => 'Retour'])
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#221a4c">
    <title>{{ $title ? "{$title} — " : '' }}Contrôle des entrées — Univers 2 M'boulè</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23221a4c'/%3E%3Ccircle cx='16' cy='16' r='7' fill='%23f6a21e'/%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen">
    <header class="sticky top-0 z-40 bg-nuit text-white">
        <div class="mx-auto flex h-14 max-w-2xl items-center justify-between gap-3 px-4">
            <p class="flex items-center gap-2.5 font-display text-base font-extrabold">
                <span class="grid size-8 place-items-center rounded-lg bg-safran text-nuit"><x-ui.icon name="scan" class="size-4" /></span>
                Contrôle des entrées
            </p>
            <form method="POST" action="{{ route('agent.logout') }}">
                @csrf
                <button type="submit" class="inline-flex min-h-11 items-center gap-2 rounded-lg px-3 text-sm font-semibold text-white/85 hover:bg-white/10">
                    <x-ui.icon name="log-out" class="size-5" /> <span class="hidden sm:inline">Se déconnecter</span>
                </button>
            </form>
        </div>
        <nav class="mx-auto flex max-w-2xl gap-2 px-4 pb-3" aria-label="Navigation agent">
            @foreach ([['agent.scanner.index', 'Scanner', 'scan', 'agent.scanner.*'], ['agent.scans.index', 'Historique', 'list', 'agent.scans.*']] as [$route, $label, $icon, $pattern])
                <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif
                   @class([
                       'inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-xl text-sm font-semibold transition-colors',
                       'bg-safran text-nuit' => request()->routeIs($pattern),
                       'bg-white/10 text-white hover:bg-white/20' => ! request()->routeIs($pattern),
                   ])>
                    <x-ui.icon :name="$icon" class="size-5" /> {{ $label }}
                </a>
            @endforeach
        </nav>
    </header>

    <main class="mx-auto max-w-2xl px-4 py-6">
        @if ($back)
            <x-ui.back :href="$back" :label="$backLabel" class="mb-4" />
        @endif
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
