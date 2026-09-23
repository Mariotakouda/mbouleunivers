<x-layouts.agent title="Historique" :back="route('agent.scanner.index')" backLabel="Retour au scanner">
    <h1 class="text-2xl font-extrabold">Historique de mes contrôles</h1>
    <p class="mt-1 text-sourdine">Les billets que vous avez scannés, du plus récent au plus ancien.</p>

    <div class="card mt-5 overflow-hidden">
        @if ($scans->isEmpty())
            <x-admin.empty icon="scan" title="Aucun contrôle pour le moment" text="Vos scans apparaîtront ici.">
                <a href="{{ route('agent.scanner.index') }}" class="btn btn-primary"><x-ui.icon name="scan" class="size-5" /> Scanner un billet</a>
            </x-admin.empty>
        @else
            <ul class="divide-y divide-bord">
                @foreach ($scans as $scan)
                    <li class="flex items-center justify-between gap-3 px-4 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $scan->ticket->ticket_number }}</p>
                            <p class="text-sm text-sourdine">{{ $scan->scanned_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <x-ui.status :value="$scan->result" />
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="mt-6">{{ $scans->links() }}</div>
</x-layouts.agent>
