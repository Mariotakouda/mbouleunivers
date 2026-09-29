@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'back' => null,
    'backLabel' => 'Retour',
    'sticky' => false,
])
@php
    $siteName = "Univers 2 M'boulè";
    $pageTitle = $title ? "{$title} — {$siteName}" : "{$siteName} — Billetterie officielle";
    $pageDescription = $description ?? "Réservez vos billets en ligne pour le spectacle {$siteName}. Commande simple via WhatsApp, billet avec QR code.";
    $whatsapp = config('services.support.whatsapp');
    $supportEmail = config('services.support.email');

    // Raccourci réservé aux équipes déjà connectées (invisible pour les clients).
    $staff = auth()->user();
    $staffLink = match ($staff?->role) {
        'admin' => [route('admin.dashboard'), 'Administration', 'layout'],
        'agent' => [route('agent.scanner.index'), 'Contrôle des entrées', 'scan'],
        default => null,
    };
@endphp
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#221a4c">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    @if ($image)
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23221a4c'/%3E%3Ccircle cx='16' cy='16' r='7' fill='%23f6a21e'/%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:font-semibold">
        Aller au contenu
    </a>

    <header class="sticky top-0 z-40 border-b border-white/10 bg-nuit text-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4">
            @if ($back)
                <x-ui.back :href="$back" :label="$backLabel" tone="dark" />
                <a href="{{ route('home') }}" class="font-display text-base font-extrabold tracking-tight sm:text-lg">
                    Univers 2 M'boulè
                </a>
            @else
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-display text-lg font-extrabold tracking-tight">
                    <span class="grid size-8 place-items-center rounded-lg bg-safran text-nuit"><x-ui.icon name="mic" class="size-4" /></span>
                    Univers 2 M'boulè
                </a>
                @isset($nav)
                    <nav>{{ $nav }}</nav>
                @endisset
            @endif

            @if ($staffLink)
                <a href="{{ $staffLink[0] }}" class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-full bg-safran px-3 text-sm font-bold text-nuit hover:brightness-95 sm:px-4"
                   aria-label="{{ $staffLink[1] }}" title="{{ $staffLink[1] }}">
                    <x-ui.icon :name="$staffLink[2]" class="size-5" />
                    <span class="hidden sm:inline">{{ $staffLink[1] }}</span>
                </a>
            @endif
        </div>
    </header>

    <main id="contenu" class="flex-1">
        {{ $slot }}
    </main>

    {{-- pb-24 : laisse la place à la barre d'achat fixe en bas de l'écran sur mobile --}}
    <footer @class(['bg-minuit text-white/70', 'pb-24 md:pb-0' => $sticky])>
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-10 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="font-display text-lg font-extrabold text-white">Univers 2 M'boulè</p>
                <p class="mt-1 text-sm">Billetterie officielle du spectacle.</p>
            </div>

            <div class="flex flex-col gap-3 text-sm md:items-end">
                <p class="inline-flex items-center gap-2">
                    <x-ui.icon name="message-circle" class="size-5 text-safran" />
                    Commande et paiement en direct avec l'organisateur
                </p>
                @if ($whatsapp || $supportEmail)
                    <p class="flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span>Une question ?</span>
                        @if ($whatsapp)
                            <a class="font-semibold text-white underline decoration-safran underline-offset-4" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener">WhatsApp</a>
                        @endif
                        @if ($supportEmail)
                            <a class="font-semibold text-white underline decoration-safran underline-offset-4" href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                        @endif
                    </p>
                @endif
            </div>
        </div>
        <div class="border-t border-white/10 px-4 py-4 text-center text-xs text-white/50">
            <span>&copy; {{ date('Y') }} Univers 2 M'boulè. Tous droits réservés.</span>
            <span class="mx-2 hidden sm:inline" aria-hidden="true">·</span>
            <span class="mt-2 block sm:mt-0 sm:inline">
                @if ($staffLink)
                    <a href="{{ $staffLink[0] }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:text-white hover:underline sm:min-h-0">{{ $staffLink[1] }}</a>
                @else
                    <a href="{{ route('admin.login') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:text-white hover:underline sm:min-h-0">Espace équipe</a>
                    <span class="mx-1" aria-hidden="true">/</span>
                    <a href="{{ route('agent.login') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:text-white hover:underline sm:min-h-0">Contrôle des entrées</a>
                @endif
            </span>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
