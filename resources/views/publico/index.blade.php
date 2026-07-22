<x-app-layout>
    @php
        $empresaSistema = \App\Support\ConfiguracionEmpresa::obtener();
    @endphp

    <main class="app-fondo flex min-h-screen flex-col text-zinc-950">
        <header class="app-bar sticky top-0 z-20 border-b border-white/10">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <a href="{{ route('publico.index') }}" class="inline-flex items-center gap-3 text-xl font-bold tracking-tight">
                    @if ($empresaSistema->imagen)
                        <img src="{{ $empresaSistema->imagen }}" alt="Icono {{ $empresaSistema->nombre }}" class="h-9 w-9 rounded-md bg-white object-cover">
                    @endif
                    {{ $empresaSistema->nombre }}
                </a>

                @auth
                    <a href="{{ route('panel.dashboard') }}" class="inline-flex items-center gap-2 rounded-md bg-white/15 px-4 py-2 text-sm font-semibold transition hover:bg-white/25">
                        <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                        Ir al panel
                    </a>
                @else
                    <a href="{{ route('sesiones.login') }}" class="inline-flex items-center gap-2 rounded-md bg-white px-4 py-2 text-sm font-semibold text-zinc-950 transition hover:bg-white/90">
                        <i data-lucide="log-in" class="h-4 w-4"></i>
                        Iniciar sesion
                    </a>
                @endauth
            </div>
        </header>

        <section class="mx-auto w-full max-w-7xl flex-1 px-6 py-10">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] texto-marca">Temporadas actuales</p>
                <h1 class="mt-3 text-4xl font-bold tracking-tight">Consulta las ligas activas de tu localidad.</h1>
                <p class="mt-4 text-zinc-600">{{ $empresaSistema->descripcion ?: 'Revisa tablas, resultados y estadisticas publicas conforme se registren partidos en cada temporada.' }}</p>
            </div>

            @if ($temporadasActuales->isEmpty())
                <div class="mt-10 rounded-lg border border-dashed border-zinc-300 bg-white px-6 py-12 text-center">
                    <p class="text-lg font-semibold text-zinc-700">Sin datos</p>
                    <p class="mt-2 text-sm text-zinc-500">Aun no hay temporadas actuales registradas.</p>
                </div>
            @else
                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($temporadasActuales as $temporada)
                        <a href="{{ route('publico.temporadas.tabla', $temporada->id) }}" class="group rounded-lg border border-zinc-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium texto-marca">{{ $temporada->liga_nombre }}</p>
                                    <h2 class="mt-2 text-xl font-bold">{{ $temporada->nombre }}</h2>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold transition menu-activo group-hover:brightness-95">
                                    Activa
                                </span>
                            </div>

                            <div class="mt-6 space-y-2 text-sm text-zinc-600">
                                <p>{{ $temporada->eslogan ?: 'Sin eslogan registrado' }}</p>
                                <p>
                                    {{ $temporada->desde ?: 'Inicio abierto' }}
                                    <span class="text-zinc-400">/</span>
                                    {{ $temporada->hasta ?: 'Fin abierto' }}
                                </p>
                            </div>

                            <div class="mt-6 text-sm font-semibold text-zinc-950">
                                Ver tabla
                                <span class="inline-block transition group-hover:translate-x-1">&rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <x-public-footer />
    </main>
</x-app-layout>
