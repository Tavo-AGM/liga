<x-app-layout>
    <main class="app-fondo min-h-screen text-zinc-950">
        <header class="border-b border-zinc-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] texto-marca">Panel de equipo</p>
                    <h1 class="text-2xl font-bold">{{ $equipoAsignado->nombre ?? 'Equipo sin asignar' }}</h1>
                    <p class="mt-1 text-sm text-zinc-500">{{ $equiposAsignados->count() }} equipo(s) vinculados a tu usuario</p>
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

        <section class="mx-auto grid max-w-7xl gap-6 px-6 py-8 lg:grid-cols-[1.35fr_.65fr]">
            <div class="space-y-6">
                @if (session('mensaje'))
                    <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('mensaje') }}</div>
                @endif

                @if ($traspasosRecibidos->isNotEmpty())
                    <section class="rounded-lg border border-amber-200 bg-amber-50 p-6">
                        <h2 class="text-lg font-semibold text-amber-950">Solicitudes pendientes</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($traspasosRecibidos as $traspaso)
                                <article class="rounded-md border border-amber-200 bg-white p-4">
                                    <p class="font-semibold">{{ $traspaso->jugador_nombre }}</p>
                                    <p class="mt-1 text-sm text-zinc-600">{{ $traspaso->equipo_origen_nombre ?: 'Jugador libre' }} -> {{ $traspaso->equipo_destino_nombre }}</p>
                                    <p class="mt-1 text-xs text-zinc-500">{{ $traspaso->mensaje ?: 'Sin mensaje' }}</p>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('encargados.traspasos.responder', $traspaso->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="aprobado">
                                            <button class="btn-primary inline-flex items-center gap-2 rounded-md px-3 py-2 text-xs font-semibold">
                                                <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                                Aprobar
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('encargados.traspasos.responder', $traspaso->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="rechazado">
                                            <button class="inline-flex items-center gap-2 rounded-md border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-50">
                                                <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                                Rechazar
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Plantilla vinculada</h2>
                    <div class="mt-5 overflow-hidden rounded-lg border border-zinc-200">
                        <div class="overflow-x-auto" data-tabla-contenedor>
                        <table class="w-full text-left text-sm" data-tabla-buscable data-tabla-filas="8">
                            <thead class="tabla-encabezado">
                                <tr>
                                    <th class="px-4 py-3">Jugador</th>
                                    <th class="px-4 py-3">Equipo</th>
                                    <th class="px-4 py-3">Liga</th>
                                    <th class="px-4 py-3">Temporada</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white">
                                @forelse ($jugadores as $jugador)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold">{{ trim($jugador->nombre.' '.$jugador->apellido_paterno.' '.$jugador->apellido_materno) }}</td>
                                        <td class="px-4 py-3">{{ $jugador->equipo_nombre ?: 'Sin equipo' }}</td>
                                        <td class="px-4 py-3">{{ $jugador->liga_nombre ?: 'Sin liga' }}</td>
                                        <td class="px-4 py-3">{{ $jugador->temporada_nombre ?: 'Sin temporada' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center text-zinc-500">Sin jugadores registrados en tus equipos</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Solicitar traspaso</h2>
                    <form method="POST" action="{{ route('encargados.traspasos.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-zinc-700">Jugador</label>
                            <select name="jugador_id" required class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                                <option value="">Selecciona un jugador</option>
                                @foreach ($jugadoresDisponibles as $jugador)
                                    <option value="{{ $jugador->id }}">{{ $jugador->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-zinc-700">Equipo destino</label>
                            <select name="equipo_destino_id" required class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                                <option value="">Selecciona tu equipo</option>
                                @foreach ($equiposAsignados as $equipo)
                                    <option value="{{ $equipo->id }}">{{ $equipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-zinc-700">Liga destino</label>
                            <select name="liga_destino_id" class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                                <option value="">Sin liga</option>
                                @foreach ($ligasDisponibles as $liga)
                                    <option value="{{ $liga->id }}">{{ $liga->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-zinc-700">Temporada</label>
                            <select name="temporada_id" class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                                <option value="">Sin temporada</option>
                                @foreach ($temporadasDisponibles as $temporada)
                                    <option value="{{ $temporada->id }}">{{ $temporada->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-zinc-700">Mensaje</label>
                            <textarea name="mensaje" rows="3" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm" placeholder="Motivo o comentario para el otro encargado"></textarea>
                        </div>

                        <div class="sm:col-span-2 flex justify-end">
                            <button class="btn-primary inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold">
                                <i data-lucide="send" class="h-4 w-4"></i>
                                Enviar solicitud
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Mis equipos</h2>
                    <div class="mt-5 space-y-3">
                        @forelse ($equiposAsignados as $equipo)
                            <div class="rounded-md bg-zinc-50 px-3 py-2 text-sm font-semibold text-zinc-700">{{ $equipo->nombre }}</div>
                        @empty
                            <p class="text-sm text-zinc-500">Sin equipos vinculados.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Traspasos enviados</h2>
                    <div class="mt-5 space-y-3">
                        @forelse ($traspasosEnviados as $traspaso)
                            <article class="rounded-md bg-zinc-50 px-3 py-2 text-sm">
                                <p class="font-semibold">{{ $traspaso->jugador_nombre }}</p>
                                <p class="text-xs text-zinc-500">{{ $traspaso->equipo_destino_nombre }} / {{ ucfirst($traspaso->estado) }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-zinc-500">Sin solicitudes enviadas.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Gestion del equipo</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ($accionesEquipo as $accion)
                            <div class="rounded-md bg-zinc-50 px-3 py-2 text-sm font-semibold text-zinc-700">{{ $accion }}</div>
                        @endforeach
                    </div>
                </section>
            </aside>
        </section>
    </main>
</x-app-layout>
