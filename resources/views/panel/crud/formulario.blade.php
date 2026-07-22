<x-panel-layout>
    <div class="mx-auto max-w-3xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">{{ $tituloFormulario }}</h1>
                <p class="mt-2 text-sm text-zinc-600">Completa los datos requeridos y guarda los cambios.</p>
            </div>
            <a href="{{ $rutaRegreso }}" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Volver
            </a>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Revisa los campos marcados antes de guardar.
            </div>
        @endif

        <form method="POST" action="{{ $accionFormulario }}" enctype="multipart/form-data" class="mt-6 rounded-lg border border-zinc-200 bg-white p-6">
            @csrf
            @if ($metodoFormulario !== 'POST')
                @method($metodoFormulario)
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ($campos as $campo)
                    @php
                        $nombreCampo = $campo['nombre'];
                        $valorCampo = old($nombreCampo, data_get($registro, $nombreCampo, $campo['valorDefecto'] ?? ''));
                    @endphp

                    <div class="{{ in_array(($campo['tipo'] ?? 'text'), ['textarea', 'multiselect'], true) ? 'sm:col-span-2' : '' }}">
                        <label for="{{ $nombreCampo }}" class="block text-sm font-semibold text-zinc-700">
                            {{ $campo['etiqueta'] }}
                            @if ($campo['requerido'] ?? false)
                                <span class="text-red-600">*</span>
                            @endif
                        </label>

                        @if (($campo['tipo'] ?? 'text') === 'textarea')
                            <textarea id="{{ $nombreCampo }}" name="{{ $nombreCampo }}" rows="4" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">{{ $valorCampo }}</textarea>
                        @elseif (($campo['tipo'] ?? 'text') === 'select')
                            <select id="{{ $nombreCampo }}" name="{{ $nombreCampo }}" class="mt-2 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">Selecciona una opcion</option>
                                @foreach (($campo['opciones'] ?? []) as $valorOpcion => $textoOpcion)
                                    <option value="{{ $valorOpcion }}" @selected((string) $valorCampo === (string) $valorOpcion)>{{ $textoOpcion }}</option>
                                @endforeach
                            </select>
                        @elseif (($campo['tipo'] ?? 'text') === 'multiselect')
                            @php
                                $valoresCampo = old($nombreCampo, $campo['valoresSeleccionados'] ?? []);
                                $valoresCampo = is_array($valoresCampo) ? $valoresCampo : [];
                            @endphp
                            <select id="{{ $nombreCampo }}" name="{{ $nombreCampo }}[]" multiple class="mt-2 min-h-32 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-[var(--color-primario)] focus:ring-2 focus:ring-[var(--color-acento)]/20">
                                @foreach (($campo['opciones'] ?? []) as $valorOpcion => $textoOpcion)
                                    <option value="{{ $valorOpcion }}" @selected(in_array((string) $valorOpcion, array_map('strval', $valoresCampo), true))>{{ $textoOpcion }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-zinc-500">{{ $campo['ayuda'] ?? 'Puedes seleccionar varias opciones manteniendo Ctrl o Shift.' }}</p>
                        @elseif (($campo['tipo'] ?? 'text') === 'file')
                            @if ($valorCampo)
                                <img src="{{ $valorCampo }}" alt="Imagen actual" class="mt-2 h-20 w-20 rounded-md border border-zinc-200 object-cover">
                            @endif
                            <input id="{{ $nombreCampo }}" name="{{ $nombreCampo }}" type="file" accept="image/jpeg,image/png" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition file:mr-4 file:rounded-md file:border-0 file:bg-[var(--color-secundario)] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white focus:border-[var(--color-primario)] focus:ring-2 focus:ring-[var(--color-acento)]/20">
                            <p class="mt-2 text-xs text-zinc-500">JPG o PNG recomendado: 800x800 px. Permitido: 300x300 a 1200x1200 px, maximo 2 MB.</p>
                        @else
                            <input id="{{ $nombreCampo }}" name="{{ $nombreCampo }}" type="{{ $campo['tipo'] ?? 'text' }}" value="{{ ($campo['tipo'] ?? 'text') === 'password' ? '' : $valorCampo }}" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        @endif

                        @error($nombreCampo)
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ $rutaRegreso }}" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition">
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
