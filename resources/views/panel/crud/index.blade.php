<x-panel-layout>
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold">{{ $tituloModulo }}</h1>
                <p class="mt-2 text-sm text-zinc-600">{{ $descripcionModulo }}</p>
            </div>
            <a href="{{ $rutaCrear }}" class="btn-primary inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Crear
            </a>
        </div>

        @if (session('mensaje'))
            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('mensaje') }}
            </div>
        @endif

        <section class="mt-6 overflow-hidden rounded-lg border border-zinc-200 bg-white">
            <div class="overflow-x-auto" data-tabla-contenedor>
                <table class="w-full text-left text-sm" data-tabla-buscable data-tabla-paginacion="servidor">
                    <thead class="tabla-encabezado">
                        <tr>
                            @foreach ($columnas as $columna)
                                <th class="px-4 py-3">{{ $columna['etiqueta'] }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($registros as $registro)
                            <tr class="transition hover:bg-zinc-50">
                                @foreach ($columnas as $columna)
                                    <td class="max-w-xs truncate px-4 py-3">
                                        @if (($columna['tipo'] ?? null) === 'imagen')
                                            @if (data_get($registro, $columna['campo']))
                                                <img src="{{ data_get($registro, $columna['campo']) }}" alt="Imagen de {{ data_get($registro, 'nombre') ?? 'registro' }}" class="h-12 w-12 rounded-md border border-zinc-200 object-cover">
                                            @else
                                                <span class="text-zinc-500">Sin datos</span>
                                            @endif
                                        @else
                                            {{ data_get($registro, $columna['campo']) ?: 'Sin datos' }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route($rutaEditarNombre, $registro->id) }}" class="inline-flex items-center gap-1.5 rounded-md border border-zinc-300 px-3 py-1.5 text-xs font-semibold text-zinc-700 transition hover:border-zinc-500 hover:text-zinc-950">
                                            <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                            Editar
                                        </a>
                                        @if (data_get($registro, 'puedeEliminar', true))
                                            <button type="button" onclick="abrirModalEliminar(@js(route($rutaEliminarNombre, $registro->id)), @js(data_get($registro, 'nombre') ?? data_get($registro, 'nombreCompleto') ?? 'registro'))" class="inline-flex items-center gap-1.5 rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:border-red-400 hover:bg-red-50">
                                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                Eliminar
                                            </button>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-md border border-zinc-200 px-3 py-1.5 text-xs font-semibold text-zinc-500">
                                                <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                                                Protegido
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columnas) + 1 }}" class="px-4 py-10 text-center text-zinc-500">
                                    Sin datos
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-5">
            {{ $registros->links() }}
        </div>
    </div>

    <dialog id="modalEliminar" class="w-full max-w-md rounded-lg p-0 shadow-2xl backdrop:bg-zinc-950/60">
        <form id="formularioEliminar" method="POST" class="bg-white p-6">
            @csrf
            @method('DELETE')
            <h2 class="text-xl font-bold text-zinc-950">Confirmar eliminacion</h2>
            <p class="mt-3 text-sm leading-6 text-zinc-600">
                Esta accion eliminara <span id="nombreEliminar" class="font-semibold text-zinc-950"></span>. Revisa antes de continuar.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="cerrarModalEliminar()" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition">
                    <i data-lucide="x" class="h-4 w-4"></i>
                    Cancelar
                </button>
                <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                    Eliminar
                </button>
            </div>
        </form>
    </dialog>

    <script>
        function abrirModalEliminar(rutaEliminar, nombreRegistro) {
            document.getElementById('formularioEliminar').setAttribute('action', rutaEliminar);
            document.getElementById('nombreEliminar').textContent = nombreRegistro;
            document.getElementById('modalEliminar').showModal();
        }

        function cerrarModalEliminar() {
            document.getElementById('modalEliminar').close();
        }
    </script>
</x-panel-layout>
