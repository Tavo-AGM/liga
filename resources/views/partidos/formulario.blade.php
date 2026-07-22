<x-panel-layout>
    <div class="mx-auto max-w-3xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">Editar partido</h1>
                <p class="mt-2 text-sm text-zinc-600">{{ $partidoActual->equipo_local_nombre ?: 'Local' }} vs {{ $partidoActual->equipo_visitante_nombre ?: 'Visitante' }}</p>
            </div>
            <a href="{{ route('gestion.partidos.index') }}" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Volver
            </a>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('gestion.partidos.update', $partidoActual->id) }}" class="mt-6 rounded-lg border border-zinc-200 bg-white p-6">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-zinc-700">Fecha</label>
                    <input name="fecha" type="date" value="{{ old('fecha', $partidoActual->fecha) }}" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700">Hora</label>
                    <input name="hora" type="time" value="{{ old('hora', $partidoActual->hora ? substr($partidoActual->hora, 0, 5) : '') }}" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700">Campo</label>
                    <select name="campo_id" class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                        <option value="">Sin campo</option>
                        @foreach ($campos as $campoId => $campoNombre)
                            <option value="{{ $campoId }}" @selected((string) old('campo_id', $partidoActual->campo_id) === (string) $campoId)>{{ $campoNombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-zinc-700">Estado</label>
                    <select name="estado" class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm">
                        @foreach ($estados as $estadoValor => $estadoTexto)
                            <option value="{{ $estadoValor }}" @selected(old('estado', $partidoActual->estado) === $estadoValor)>{{ $estadoTexto }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('gestion.partidos.index') }}" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition">
                    <i data-lucide="x" class="h-4 w-4"></i>
                    Cancelar
                </a>
                <button type="submit" class="btn-primary inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Guardar
                </button>
            </div>
        </form>
    </div>
</x-panel-layout>
