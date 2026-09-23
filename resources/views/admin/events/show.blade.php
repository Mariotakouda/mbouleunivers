<x-layouts.admin :title="$event->title">
    <x-admin.header :title="$event->title" :back="route('admin.events.index')" backLabel="Liste des spectacles">
        <a href="{{ route('events.show', $event) }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm"><x-ui.icon name="external" class="size-4" /> Voir sur le site</a>
        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-dark btn-sm"><x-ui.icon name="edit" class="size-4" /> Modifier</a>
    </x-admin.header>

    @if ($event->status !== 'published')
        <x-ui.alert type="warning" class="mb-6">
            Ce spectacle n'est pas publié : il n'apparaît pas sur le site. <a href="{{ route('admin.events.edit', $event) }}" class="font-semibold underline">Le publier</a>
        </x-ui.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
        <div class="card p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <x-ui.status :value="$event->status" />
            </div>
            <p class="mt-4 whitespace-pre-line leading-relaxed text-sourdine">{{ $event->description }}</p>

            <dl class="mt-6 grid gap-5 border-t border-bord pt-6 sm:grid-cols-3">
                <div class="flex gap-3">
                    <x-ui.icon name="calendar" class="mt-0.5 size-5 shrink-0 text-safran-vif" />
                    <div><dt class="text-sm text-sourdine">Date</dt><dd class="font-semibold">{{ $event->dateLong() }}</dd></div>
                </div>
                <div class="flex gap-3">
                    <x-ui.icon name="clock" class="mt-0.5 size-5 shrink-0 text-safran-vif" />
                    <div><dt class="text-sm text-sourdine">Horaires</dt><dd class="font-semibold">{{ $event->startTimeLabel() }}@if ($event->endTimeLabel()) – {{ $event->endTimeLabel() }}@endif</dd></div>
                </div>
                <div class="flex gap-3">
                    <x-ui.icon name="map-pin" class="mt-0.5 size-5 shrink-0 text-safran-vif" />
                    <div><dt class="text-sm text-sourdine">Lieu</dt><dd class="font-semibold">{{ $event->venue }}@if ($event->address)<span class="block font-normal text-sourdine">{{ $event->address }}</span>@endif</dd></div>
                </div>
            </dl>
        </div>

        @if ($event->image)
            <img src="{{ Storage::url($event->image) }}" alt="Affiche : {{ $event->title }}" class="w-full max-w-xs rounded-2xl ring-1 ring-bord lg:max-w-none">
        @endif
    </div>

    <section class="mt-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-extrabold">Catégories de billets</h2>
            <a href="{{ route('admin.ticket-types.create', ['event_id' => $event->id]) }}" class="btn btn-primary btn-sm"><x-ui.icon name="plus" class="size-4" /> Ajouter une catégorie</a>
        </div>

        <div class="card overflow-hidden">
            @if ($event->ticketTypes->isEmpty())
                <x-admin.empty icon="ticket" title="Aucune catégorie de billets" text="Sans catégorie, personne ne peut acheter de billet. Ajoutez par exemple Standard, VIP et Premium.">
                    <a href="{{ route('admin.ticket-types.create', ['event_id' => $event->id]) }}" class="btn btn-primary"><x-ui.icon name="plus" class="size-5" /> Ajouter une catégorie</a>
                </x-admin.empty>
            @else
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>Catégorie</th><th class="text-right">Prix</th><th>Ventes</th><th>Statut</th><th><span class="sr-only">Actions</span></th></tr></thead>
                        <tbody>
                            @foreach ($event->ticketTypes as $type)
                                @php $sold = $type->quantity - $type->available_quantity; $pct = $type->quantity > 0 ? (int) round($sold / $type->quantity * 100) : 0; @endphp
                                <tr>
                                    <td class="font-semibold">{{ $type->name }}</td>
                                    <td class="whitespace-nowrap text-right tabular-nums">{{ number_format($type->price, 0, ',', ' ') }} FCFA</td>
                                    <td class="min-w-40">
                                        <div class="h-2 overflow-hidden rounded-full bg-craie ring-1 ring-bord"><div class="h-full rounded-full bg-safran" style="width: {{ $pct }}%"></div></div>
                                        <p class="mt-1 text-xs text-sourdine">{{ $sold }} vendus · {{ $type->available_quantity }} restants sur {{ $type->quantity }}</p>
                                    </td>
                                    <td><x-ui.status :value="$type->status" /></td>
                                    <td class="text-right"><a href="{{ route('admin.ticket-types.edit', $type) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="edit" class="size-4" /> Modifier</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</x-layouts.admin>
