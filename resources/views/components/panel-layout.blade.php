@php
    $empresaSistema = \App\Support\ConfiguracionEmpresa::obtener();
    $elementosMenu = [
        ['nombre' => 'Dashboard', 'ruta' => 'panel.dashboard', 'patron' => 'panel.dashboard'],
        ['nombre' => 'Empresa', 'ruta' => 'gestion.empresa.edit', 'patron' => 'gestion.empresa.*'],
        ['nombre' => 'Usuarios', 'ruta' => 'gestion.usuarios.index', 'patron' => 'gestion.usuarios.*'],
        ['nombre' => 'Ligas', 'ruta' => 'gestion.ligas.index', 'patron' => 'gestion.ligas.*'],
        ['nombre' => 'Equipos', 'ruta' => 'gestion.equipos.index', 'patron' => 'gestion.equipos.*'],
        ['nombre' => 'Jugadores', 'ruta' => 'gestion.jugadores.index', 'patron' => 'gestion.jugadores.*'],
        ['nombre' => 'Plantillas', 'ruta' => 'gestion.plantillas.index', 'patron' => 'gestion.plantillas.*'],
        ['nombre' => 'Traspasos', 'ruta' => 'gestion.traspasos.index', 'patron' => 'gestion.traspasos.*'],
        ['nombre' => 'Campos', 'ruta' => 'gestion.campos.index', 'patron' => 'gestion.campos.*'],
        ['nombre' => 'Arbitros', 'ruta' => 'gestion.arbitros.index', 'patron' => 'gestion.arbitros.*'],
        ['nombre' => 'Temporadas', 'ruta' => 'gestion.temporadas.index', 'patron' => 'gestion.temporadas.*'],
        ['nombre' => 'Partidos', 'ruta' => 'gestion.partidos.index', 'patron' => 'gestion.partidos.*'],
        ['nombre' => 'Generar temporada', 'ruta' => 'gestion.calendarios.temporada.create', 'patron' => 'gestion.calendarios.*'],
        ['nombre' => 'Torneos', 'ruta' => 'gestion.torneos.generar.create', 'patron' => 'gestion.torneos.*'],
    ];
@endphp

<x-app-layout>
    <main class="app-fondo min-h-screen text-zinc-950">
        <div class="flex min-h-screen">
            <aside class="app-menu hidden w-72 border-r border-zinc-200 lg:block">
                <div class="border-b border-zinc-200 px-6 py-5">
                    <div class="flex items-center gap-3">
                        @if ($empresaSistema->imagen)
                            <img src="{{ $empresaSistema->imagen }}" alt="Icono {{ $empresaSistema->nombre }}" class="h-11 w-11 rounded-md border border-zinc-200 object-cover">
                        @endif
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] texto-marca">{{ $empresaSistema->nombre }}</p>
                            <h1 class="mt-1 text-xl font-bold">{{ $empresaSistema->eslogan ?: 'Panel admin' }}</h1>
                        </div>
                    </div>
                </div>

                <nav class="space-y-1 px-3 py-4">
                    @foreach ($elementosMenu as $elementoMenu)
                        <a href="{{ route($elementoMenu['ruta']) }}" class="block rounded-md px-3 py-2 text-sm font-semibold transition {{ request()->routeIs($elementoMenu['patron']) ? 'menu-activo' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}">
                            {{ $elementoMenu['nombre'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <section class="min-w-0 flex-1">
                <header class="app-menu border-b border-zinc-200">
                    <div class="flex items-center justify-between gap-4 px-6 py-4">
                        <div>
                            <p class="text-sm text-zinc-500">Sesion administrativa</p>
                            <p class="font-semibold">{{ auth()->user()->nombre ?? 'Administrador' }}</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('publico.index') }}" class="inline-flex items-center gap-2 rounded-md border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:border-zinc-500 hover:text-zinc-950">
                                <i data-lucide="external-link" class="h-4 w-4"></i>
                                Ver sitio
                            </a>
                            <form method="POST" action="{{ route('sesiones.cerrar') }}">
                                @csrf
                                <button type="submit" class="btn-primary inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition">
                                    <i data-lucide="log-out" class="h-4 w-4"></i>
                                    Cerrar sesion
                                </button>
                            </form>
                        </div>
                    </div>

                    <nav class="flex gap-2 overflow-x-auto border-t border-zinc-100 px-6 py-3 lg:hidden">
                        @foreach ($elementosMenu as $elementoMenu)
                            <a href="{{ route($elementoMenu['ruta']) }}" class="shrink-0 rounded-md px-3 py-2 text-sm font-semibold {{ request()->routeIs($elementoMenu['patron']) ? 'menu-activo' : 'text-zinc-600' }}">
                                {{ $elementoMenu['nombre'] }}
                            </a>
                        @endforeach
                    </nav>
                </header>

                <div class="px-6 py-8">
                    {{ $slot }}
                </div>
            </section>
        </div>
    </main>
</x-app-layout>
