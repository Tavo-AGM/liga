<x-panel-layout>
    <div class="mx-auto max-w-6xl">
        <div>
            <h1 class="text-3xl font-bold">Generar partidos de temporada</h1>
            <p class="mt-2 text-sm text-zinc-600">Crea el calendario completo de una temporada considerando campos, horarios, dias y arbitros.</p>
        </div>

        @if (session('mensaje'))
            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('mensaje') }}</div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('gestion.calendarios.temporada.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[1fr_.9fr]">
            @csrf

            <section class="rounded-lg border border-zinc-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Configuracion</h2>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700">Temporada</label>
                        <select name="temporada_id" class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                            <option value="">Selecciona una temporada</option>
                            @foreach ($temporadas as $temporada)
                                <option value="{{ $temporada->id }}" @selected(old('temporada_id') == $temporada->id)>{{ $temporada->liga_nombre ?: 'Liga sin nombre' }} / {{ $temporada->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700">Jornadas</label>
                        <select name="ida_vuelta" class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                            <option value="0">Una vuelta</option>
                            <option value="1">Ida y vuelta</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-700">Tolerancia por campo</label>
                        <select name="tolerancia_horas" class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                            <option value="2">2 horas</option>
                            <option value="3">3 horas</option>
                            <option value="4">4 horas</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-zinc-700">Horarios</label>
                        <textarea name="horarios" rows="3" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm" placeholder="09:00, 11:30, 14:00">{{ old('horarios', '09:00, 11:30, 14:00') }}</textarea>
                        <p class="mt-2 text-xs text-zinc-500">Usa formato 24 horas HH:MM, separado por coma o salto de linea.</p>
                    </div>
                </div>

                <div class="mt-5">
                    <p class="text-sm font-semibold text-zinc-700">Dias de juego</p>
                    <div class="mt-2 grid gap-2 sm:grid-cols-4">
                        @foreach ($diasSemana as $valorDia => $nombreDia)
                            <label class="flex items-center gap-2 rounded-md border border-zinc-200 px-3 py-2 text-sm">
                                <input type="checkbox" name="dias_juego[]" value="{{ $valorDia }}" @checked(in_array((string) $valorDia, old('dias_juego', ['6', '0']), true))>
                                {{ $nombreDia }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="space-y-6">
                <div class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Equipos participantes</h2>
                    <div class="mt-4 max-h-72 space-y-2 overflow-auto">
                        @forelse ($equipos as $equipo)
                            <label class="flex items-center justify-between gap-3 rounded-md border border-zinc-200 px-3 py-2 text-sm">
                                <span>
                                    <span class="font-semibold">{{ $equipo->nombre }}</span>
                                    <span class="ml-2 text-zinc-500">{{ $equipo->campo_nombre ?: 'Sin campo' }}</span>
                                </span>
                                <input type="checkbox" name="equipos[]" value="{{ $equipo->id }}" @checked(in_array($equipo->id, old('equipos', [])))>
                            </label>
                        @empty
                            <p class="text-sm text-zinc-500">Sin equipos registrados.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Arbitros disponibles</h2>
                    <div class="mt-4 max-h-48 space-y-2 overflow-auto">
                        @forelse ($arbitros as $arbitro)
                            <label class="flex items-center justify-between gap-3 rounded-md border border-zinc-200 px-3 py-2 text-sm">
                                {{ trim($arbitro->nombre.' '.$arbitro->apellido_paterno.' '.$arbitro->apellido_materno) }}
                                <input type="checkbox" name="arbitros[]" value="{{ $arbitro->id }}" @checked(in_array($arbitro->id, old('arbitros', [])))>
                            </label>
                        @empty
                            <p class="text-sm text-zinc-500">Sin arbitros registrados. Puedes generar partidos sin asignarlos.</p>
                        @endforelse
                    </div>
                </div>

                <button type="submit" class="btn-primary inline-flex w-full items-center justify-center gap-2 rounded-md px-4 py-3 text-sm font-semibold transition">
                    <i data-lucide="calendar-plus" class="h-4 w-4"></i>
                    Generar calendario
                </button>
            </section>
        </form>
    </div>
</x-panel-layout>
