<x-app-layout>
    <main class="app-fondo min-h-screen text-zinc-950">
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] texto-marca">Panel de arbitro</p>
                    <h1 class="text-2xl font-bold">Mis partidos asignados</h1>
                </div>
                <form method="POST" action="{{ route('sesiones.cerrar') }}">
                    @csrf
                    <button class="btn-primary inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                        Cerrar sesion
                    </button>
                </form>
            </div>
        </header>

        <section class="mx-auto grid max-w-7xl gap-6 px-6 py-8 lg:grid-cols-[1.4fr_.6fr]">
            <div class="rounded-lg border border-zinc-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Proximos encuentros</h2>
                <div class="mt-5 overflow-hidden rounded-lg border border-zinc-200">
                    <div class="overflow-x-auto" data-tabla-contenedor>
                        <table class="w-full text-left text-sm" data-tabla-buscable data-tabla-filas="6">
                            <thead class="tabla-encabezado">
                                <tr>
                                    <th class="px-4 py-3">Liga</th>
                                    <th class="px-4 py-3">Partido</th>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Campo</th>
                                    <th class="px-4 py-3">Estado</th>
                                    <th class="px-4 py-3 text-right">Accion</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white">
                                @forelse ($proximosPartidos as $partido)
                                    <tr class="transition hover:bg-zinc-50">
                                        <td class="px-4 py-3 font-semibold">{{ $partido->liga_nombre ?: 'Liga sin definir' }}</td>
                                        <td class="px-4 py-3">{{ $partido->equipo_local_nombre ?: 'Local' }} vs {{ $partido->equipo_visitante_nombre ?: 'Visitante' }}</td>
                                        <td class="px-4 py-3">{{ $partido->fecha ?: 'Fecha pendiente' }} {{ $partido->hora ?: '' }}</td>
                                        <td class="px-4 py-3">{{ $partido->campo_nombre ?: 'Campo pendiente' }}</td>
                                        <td class="px-4 py-3">{{ str_replace('_', ' ', $partido->estado) }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <button class="btn-primary inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-xs font-semibold">
                                                <i data-lucide="clipboard-pen" class="h-3.5 w-3.5"></i>
                                                Capturar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-zinc-500">Sin partidos asignados</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <aside class="rounded-lg border border-zinc-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Acciones del partido</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($accionesEstadisticas as $accion)
                        <div class="rounded-md bg-zinc-50 px-3 py-2 text-sm font-semibold text-zinc-700">{{ $accion }}</div>
                    @endforeach
                </div>
            </aside>
        </section>
    </main>
</x-app-layout>
