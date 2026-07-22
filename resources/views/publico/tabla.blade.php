<x-app-layout>
    @php
        $empresaSistema = \App\Support\ConfiguracionEmpresa::obtener();
    @endphp

    <main class="app-fondo flex min-h-screen flex-col text-zinc-950">
        <header class="app-bar border-b border-white/10">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <a href="{{ route('publico.index') }}" class="inline-flex items-center gap-3 text-xl font-bold tracking-tight">
                    @if ($empresaSistema->imagen)
                        <img src="{{ $empresaSistema->imagen }}" alt="Icono {{ $empresaSistema->nombre }}" class="h-9 w-9 rounded-md bg-white object-cover">
                    @endif
                    {{ $empresaSistema->nombre }}
                </a>
                <a href="{{ route('sesiones.login') }}" class="inline-flex items-center gap-2 rounded-md bg-white px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-white/90">
                    <i data-lucide="log-in" class="h-4 w-4"></i>
                    Iniciar sesion
                </a>
            </div>
        </header>

        <section class="mx-auto w-full max-w-7xl flex-1 px-6 py-10">
            <a href="{{ route('publico.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold texto-marca hover:brightness-110">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Volver a temporadas
            </a>

            <div class="mt-5">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] texto-marca">{{ $temporadaActual->liga_nombre }}</p>
                <h1 class="mt-3 text-4xl font-bold tracking-tight">Tabla de {{ $temporadaActual->nombre }}</h1>
                <p class="mt-3 text-zinc-600">{{ $temporadaActual->eslogan ?: 'Sin eslogan registrado' }}</p>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-[1fr_360px]">
                <section class="overflow-hidden rounded-lg border border-zinc-200 bg-white">
                    <div class="border-b border-zinc-200 px-5 py-4">
                        <h2 class="text-lg font-semibold">Tabla general</h2>
                    </div>

                    @if (empty($tablaEquipos))
                        <div class="px-6 py-12 text-center">
                            <p class="text-lg font-semibold text-zinc-700">Sin datos</p>
                            <p class="mt-2 text-sm text-zinc-500">Aun no hay equipos vinculados por partidos o torneos.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="tabla-encabezado">
                                    <tr>
                                        <th class="px-4 py-3">#</th>
                                        <th class="px-4 py-3">Logo</th>
                                        <th class="px-4 py-3">Equipo</th>
                                        <th class="px-4 py-3 text-center">J</th>
                                        <th class="px-4 py-3 text-center">G</th>
                                        <th class="px-4 py-3 text-center">E</th>
                                        <th class="px-4 py-3 text-center">P</th>
                                        <th class="px-4 py-3 text-center">GF</th>
                                        <th class="px-4 py-3 text-center">GC</th>
                                        <th class="px-4 py-3 text-center">DIF</th>
                                        <th class="px-4 py-3 text-center">PTS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200">
                                    @foreach ($tablaEquipos as $indice => $equipo)
                                        <tr class="transition hover:bg-zinc-50">
                                            <td class="px-4 py-3 font-semibold">{{ $indice + 1 }}</td>
                                            <td class="px-4 py-3">
                                                @if ($equipo['imagen'])
                                                    <img src="{{ $equipo['imagen'] }}" alt="Logo {{ $equipo['nombre'] }}" class="h-9 w-9 rounded-md border border-zinc-200 object-cover">
                                                @else
                                                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-zinc-100 text-xs font-bold text-zinc-500">{{ mb_substr($equipo['nombre'], 0, 2) }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 font-semibold">{{ $equipo['nombre'] }}</td>
                                            <td class="px-4 py-3 text-center">{{ $equipo['jugados'] }}</td>
                                            <td class="px-4 py-3 text-center">{{ $equipo['ganados'] }}</td>
                                            <td class="px-4 py-3 text-center">{{ $equipo['empatados'] }}</td>
                                            <td class="px-4 py-3 text-center">{{ $equipo['perdidos'] }}</td>
                                            <td class="px-4 py-3 text-center">{{ $equipo['golesFavor'] }}</td>
                                            <td class="px-4 py-3 text-center">{{ $equipo['golesContra'] }}</td>
                                            <td class="px-4 py-3 text-center">{{ $equipo['diferencia'] }}</td>
                                            <td class="px-4 py-3 text-center font-bold">{{ $equipo['puntos'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                <aside class="space-y-6">
                    <section class="rounded-lg border border-zinc-200 bg-white p-5">
                        <h2 class="text-lg font-semibold">Lideres</h2>

                        <div class="mt-5">
                            <h3 class="text-sm font-bold uppercase tracking-[0.12em] texto-marca">Goleadores</h3>
                            <div class="mt-3 space-y-2">
                                @forelse ($lideresGoleadores as $jugador)
                                    <div class="flex items-center justify-between rounded-md bg-zinc-50 px-3 py-2 text-sm">
                                        <span>{{ trim($jugador->nombre.' '.$jugador->apellido_paterno.' '.$jugador->apellido_materno) }}</span>
                                        <span class="font-bold">{{ $jugador->total }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-zinc-500">Sin datos</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-sm font-bold uppercase tracking-[0.12em] texto-marca">Asistidores</h3>
                            <div class="mt-3 space-y-2">
                                @forelse ($lideresAsistidores as $jugador)
                                    <div class="flex items-center justify-between rounded-md bg-zinc-50 px-3 py-2 text-sm">
                                        <span>{{ trim($jugador->nombre.' '.$jugador->apellido_paterno.' '.$jugador->apellido_materno) }}</span>
                                        <span class="font-bold">{{ $jugador->total }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-zinc-500">Sin datos</p>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    <section class="rounded-lg border border-zinc-200 bg-white p-5">
                        <h2 class="text-lg font-semibold">Proximos partidos</h2>
                        <div class="mt-4 space-y-3">
                            @forelse ($proximosPartidos as $partido)
                                <article class="rounded-md border border-zinc-200 p-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em] texto-marca">{{ $partido->torneo_nombre ?: ($partido->fase ?: 'Temporada') }}</p>
                                    <h3 class="mt-1 text-sm font-bold">{{ $partido->equipo_local_nombre ?: 'Local' }} vs {{ $partido->equipo_visitante_nombre ?: 'Visitante' }}</h3>
                                    <p class="mt-2 text-xs text-zinc-500">{{ $partido->fecha ?: 'Fecha pendiente' }} {{ $partido->hora ?: '' }} / {{ $partido->campo_nombre ?: 'Campo pendiente' }}</p>
                                </article>
                            @empty
                                <p class="text-sm text-zinc-500">Sin partidos por jugar</p>
                            @endforelse
                        </div>
                    </section>
                </aside>
            </div>
        </section>

        <x-public-footer />
    </main>
</x-app-layout>
