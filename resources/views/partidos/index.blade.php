<x-panel-layout>
    <div class="mx-auto max-w-7xl">
        <div>
            <h1 class="text-3xl font-bold">Partidos</h1>
            <p class="mt-2 text-sm text-zinc-600">Edita fecha, hora, campo y estado de los partidos generados.</p>
        </div>

        @if (session('mensaje'))
            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('mensaje') }}</div>
        @endif

        <section class="mt-6 overflow-hidden rounded-lg border border-zinc-200 bg-white">
            <div class="overflow-x-auto" data-tabla-contenedor>
                <table class="w-full text-left text-sm" data-tabla-buscable data-tabla-paginacion="servidor">
                    <thead class="tabla-encabezado">
                        <tr>
                            <th class="px-4 py-3">Competencia</th>
                            <th class="px-4 py-3">Partido</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Campo</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($partidos as $partido)
                            <tr class="transition hover:bg-zinc-50">
                                <td class="px-4 py-3">{{ $partido->torneo_nombre ?: ($partido->temporada_nombre ?: 'Sin competencia') }}</td>
                                <td class="px-4 py-3 font-semibold">{{ $partido->equipo_local_nombre ?: 'Local' }} vs {{ $partido->equipo_visitante_nombre ?: 'Visitante' }}</td>
                                <td class="px-4 py-3">{{ $partido->fecha ?: 'Pendiente' }} {{ $partido->hora ?: '' }}</td>
                                <td class="px-4 py-3">{{ $partido->campo_nombre ?: 'Sin campo' }}</td>
                                <td class="px-4 py-3">{{ str_replace('_', ' ', $partido->estado) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('gestion.partidos.edit', $partido->id) }}" class="inline-flex items-center gap-2 rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-semibold text-zinc-700 transition hover:border-zinc-500 hover:text-zinc-950">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-zinc-500">Sin partidos generados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-5">{{ $partidos->links() }}</div>
    </div>
</x-panel-layout>
