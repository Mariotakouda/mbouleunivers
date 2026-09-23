@props(['code', 'heading', 'text'])
<x-layouts.app :title="$heading">
    <section class="stage text-white">
        <div class="mx-auto flex max-w-2xl flex-col items-center px-4 py-20 text-center md:py-28">
            <p class="font-display text-8xl font-extrabold leading-none text-safran md:text-9xl">{{ $code }}</p>
            <h1 class="mt-6 text-3xl font-extrabold md:text-4xl">{{ $heading }}</h1>
            <p class="mt-4 max-w-md leading-relaxed text-white/80">{{ $text }}</p>
            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                    <x-ui.icon name="home" class="size-5" /> Retour à l'accueil
                </a>
                <button type="button" onclick="history.length > 1 ? history.back() : location.assign('{{ route('home') }}')"
                        class="btn btn-lg border border-white/25 bg-transparent text-white hover:bg-white/10">
                    <x-ui.icon name="chevron-left" class="size-5" /> Page précédente
                </button>
            </div>
        </div>
    </section>
</x-layouts.app>
