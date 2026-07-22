<x-panel-layout>
        @php
            $empresaSistema = \App\Support\ConfiguracionEmpresa::obtener();
        @endphp

        <section class="mx-auto max-w-7xl">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.18em] texto-marca">{{ $empresaSistema->nombre }}</p>
                <h1 class="mt-1 text-3xl font-bold">Dashboard administrativo</h1>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($resumenGeneral as $etiqueta => $total)
                    <article class="rounded-lg border border-zinc-200 bg-white p-5">
                        <p class="text-sm font-medium capitalize text-zinc-500">{{ $etiqueta }}</p>
                        <p class="mt-3 text-3xl font-bold">{{ $total }}</p>
                    </article>
                @endforeach
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-[1.4fr_.6fr]">
                <section class="rounded-lg border border-zinc-200 bg-white p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold">Modulos del prototipo</h2>
                            <p class="mt-1 text-sm text-zinc-500">Base inicial para crecer hacia CRUDs completos y vistas publicas.</p>
                        </div>
                        <a href="{{ route('gestion.ligas.create') }}" class="btn-primary inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Nueva liga
                        </a>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($modulosPrincipales as $modulo)
                            <a href="{{ $modulo['ruta'] }}" class="rounded-lg border border-zinc-200 p-4 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-900/10">
                                <h3 class="font-semibold">{{ $modulo['nombre'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $modulo['descripcion'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>

                <aside class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Siguientes pasos</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ($proximasTareas as $tarea)
                            <div class="flex items-center gap-3 rounded-md bg-zinc-50 px-3 py-2 text-sm text-zinc-700">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                {{ $tarea }}
                            </div>
                        @endforeach
                    </div>
                </aside>
            </div>

            <section class="mt-8 rounded-lg border border-zinc-200 bg-white p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Partidos por jugar</h2>
                        <p class="mt-1 text-sm text-zinc-500">Proximos encuentros generados desde temporadas o torneos.</p>
                    </div>
                    <a href="{{ route('gestion.calendarios.temporada.create') }}" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition">
                        <i data-lucide="calendar-plus" class="h-4 w-4"></i>
                        Generar
                    </a>
                </div>

                <div class="mt-5 overflow-x-auto" data-tabla-contenedor>
                    <table class="w-full text-left text-sm" data-tabla-buscable data-tabla-filas="6">
                        <thead class="tabla-encabezado">
                            <tr>
                                <th class="px-4 py-3">Competencia</th>
                                <th class="px-4 py-3">Partido</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Campo</th>
                                <th class="px-4 py-3">Arbitro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($partidosPorJugar as $partido)
                                <tr class="transition hover:bg-zinc-50">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold">{{ $partido->torneo_nombre ?: ($partido->temporada_nombre ?: 'Sin competencia') }}</p>
                                        <p class="text-xs text-zinc-500">{{ $partido->fase ?: 'fase general' }} {{ $partido->grupo ? '/ '.$partido->grupo : '' }}</p>
                                    </td>
                                    <td class="px-4 py-3 font-semibold">{{ $partido->equipo_local_nombre ?: 'Local' }} vs {{ $partido->equipo_visitante_nombre ?: 'Visitante' }}</td>
                                    <td class="px-4 py-3">{{ $partido->fecha ?: 'Pendiente' }} {{ $partido->hora ?: '' }}</td>
                                    <td class="px-4 py-3">{{ $partido->campo_nombre ?: 'Sin campo' }}</td>
                                    <td class="px-4 py-3">{{ trim(($partido->arbitro_nombre ?? '').' '.($partido->arbitro_apellido ?? '')) ?: 'Sin asignar' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-zinc-500">Sin partidos por jugar</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
</x-panel-layout>
