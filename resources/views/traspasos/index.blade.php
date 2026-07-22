<x-panel-layout>
    <div class="mx-auto max-w-7xl">
        <div>
            <h1 class="text-3xl font-bold">Traspasos</h1>
            <p class="mt-2 text-sm text-zinc-600">Consulta solicitudes entre equipos y su estado.</p>
        </div>

        <section class="mt-6 overflow-hidden rounded-lg border border-zinc-200 bg-white">
            <div class="overflow-x-auto" data-tabla-contenedor>
                <table class="w-full text-left text-sm" data-tabla-buscable data-tabla-paginacion="servidor">
                    <thead class="tabla-encabezado">
                        <tr>
                            <th class="px-4 py-3">Jugador</th>
                            <th class="px-4 py-3">Origen</th>
                            <th class="px-4 py-3">Destino</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse ($traspasos as $traspaso)
                            <tr class="transition hover:bg-zinc-50">
                                <td class="px-4 py-3 font-semibold">{{ $traspaso->jugador_nombre }}</td>
                                <td class="px-4 py-3">
                                    {{ $traspaso->equipo_origen_nombre ?: 'Jugador libre' }}
                                    <span class="block text-xs text-zinc-500">{{ $traspaso->liga_origen_nombre ?: 'Sin liga' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $traspaso->equipo_destino_nombre }}
                                    <span class="block text-xs text-zinc-500">{{ $traspaso->liga_destino_nombre ?: 'Sin liga' }}</span>
                                </td>
                                <td class="px-4 py-3 font-semibold capitalize">{{ $traspaso->estado }}</td>
                                <td class="px-4 py-3">{{ $traspaso->created_at }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-zinc-500">Sin traspasos registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-5">{{ $traspasos->links() }}</div>
    </div>
</x-panel-layout>
