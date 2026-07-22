<x-panel-layout>
    <div class="mx-auto max-w-5xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-primario)]">Configuracion global</p>
                <h1 class="mt-1 text-3xl font-bold">Empresa</h1>
                <p class="mt-2 text-sm text-zinc-600">Actualiza la identidad, icono y paleta que se reflejan en todo el sistema.</p>
            </div>
            <a href="{{ route('panel.dashboard') }}" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Volver
            </a>
        </div>

        @if (session('mensaje'))
            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('mensaje') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Revisa los campos marcados antes de guardar.
            </div>
        @endif

        <form method="POST" action="{{ route('gestion.empresa.update') }}" enctype="multipart/form-data" class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
            @csrf
            @method('PUT')

            <section class="rounded-lg border border-zinc-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Datos principales</h2>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-zinc-700">Nombre <span class="text-red-600">*</span></label>
                        <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $empresaActual->nombre) }}" required class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-[var(--color-primario)] focus:ring-2 focus:ring-[var(--color-acento)]/20">
                        @error('nombre')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="eslogan" class="block text-sm font-semibold text-zinc-700">Eslogan</label>
                        <input id="eslogan" name="eslogan" type="text" value="{{ old('eslogan', $empresaActual->eslogan) }}" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-[var(--color-primario)] focus:ring-2 focus:ring-[var(--color-acento)]/20">
                        @error('eslogan')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="descripcion" class="block text-sm font-semibold text-zinc-700">Descripcion</label>
                        <textarea id="descripcion" name="descripcion" rows="4" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-[var(--color-primario)] focus:ring-2 focus:ring-[var(--color-acento)]/20">{{ old('descripcion', $empresaActual->descripcion) }}</textarea>
                        @error('descripcion')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="texto_pie_pagina" class="block text-sm font-semibold text-zinc-700">Texto del pie de pagina publico</label>
                        <textarea id="texto_pie_pagina" name="texto_pie_pagina" rows="3" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-[var(--color-primario)] focus:ring-2 focus:ring-[var(--color-acento)]/20">{{ old('texto_pie_pagina', $empresaActual->texto_pie_pagina) }}</textarea>
                        @error('texto_pie_pagina')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="imagen" class="block text-sm font-semibold text-zinc-700">Icono</label>
                        @if ($empresaActual->imagen)
                            <img src="{{ $empresaActual->imagen }}" alt="Icono actual" class="mt-2 h-20 w-20 rounded-md border border-zinc-200 object-cover">
                        @endif
                        <input id="imagen" name="imagen" type="file" accept="image/jpeg,image/png" class="mt-2 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition file:mr-4 file:rounded-md file:border-0 file:bg-[var(--color-secundario)] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white focus:border-[var(--color-primario)] focus:ring-2 focus:ring-[var(--color-acento)]/20">
                        <p class="mt-2 text-xs text-zinc-500">JPG o PNG recomendado: 512x512 px. Permitido: 300x300 a 1200x1200 px, maximo 2 MB.</p>
                        @error('imagen')
                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Paleta</h2>
                    <div class="mt-5 space-y-4">
                        @foreach ([
                            'color_primario' => 'Color principal',
                            'color_secundario' => 'Color secundario',
                            'color_acento' => 'Color de acento',
                            'color_fondo' => 'Color de fondo',
                            'color_texto' => 'Texto en appbar/botones',
                            'color_menu' => 'Color del menu',
                            'color_pie_pagina' => 'Color del pie de pagina',
                        ] as $nombreCampo => $etiquetaCampo)
                            <div>
                                <label for="{{ $nombreCampo }}" class="block text-sm font-semibold text-zinc-700">{{ $etiquetaCampo }}</label>
                                <div class="mt-2 flex gap-2">
                                    <input id="selector_{{ $nombreCampo }}" type="color" value="{{ old($nombreCampo, $empresaActual->{$nombreCampo}) }}" oninput="document.getElementById('{{ $nombreCampo }}').value = this.value" class="h-10 w-14 rounded-md border border-zinc-300 bg-white p-1">
                                    <input id="{{ $nombreCampo }}" name="{{ $nombreCampo }}" type="text" value="{{ old($nombreCampo, $empresaActual->{$nombreCampo}) }}" oninput="document.getElementById('selector_{{ $nombreCampo }}').value = this.value" class="min-w-0 flex-1 rounded-md border border-zinc-300 px-3 py-2 text-sm font-mono outline-none transition focus:border-[var(--color-primario)] focus:ring-2 focus:ring-[var(--color-acento)]/20">
                                </div>
                                @error($nombreCampo)
                                    <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Vista previa</h2>
                    <div class="mt-4 overflow-hidden rounded-md border border-zinc-200">
                        <div class="app-bar flex items-center gap-3 px-4 py-3">
                            @if ($empresaActual->imagen)
                                <img src="{{ $empresaActual->imagen }}" alt="Icono {{ $empresaActual->nombre }}" class="h-9 w-9 rounded-md bg-white object-cover">
                            @endif
                            <div>
                                <p class="font-bold">{{ $empresaActual->nombre }}</p>
                                <p class="text-xs opacity-80">{{ $empresaActual->eslogan ?: 'Sin eslogan' }}</p>
                            </div>
                        </div>
                        <div class="app-menu border-b border-zinc-200 px-4 py-3 text-sm font-semibold">
                            Menu administrativo
                        </div>
                        <div class="space-y-3 p-4">
                            <button type="button" class="btn-primary inline-flex w-full items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition">
                                <i data-lucide="save" class="h-4 w-4"></i>
                                Guardar
                            </button>
                            <button type="button" class="btn-secondary inline-flex w-full items-center justify-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition">
                                <i data-lucide="x" class="h-4 w-4"></i>
                                Cancelar
                            </button>
                        </div>
                        <div class="app-footer border-t border-zinc-200 px-4 py-3 text-xs text-zinc-600">
                            Pie de pagina publico
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Visitas publicas</h2>
                    <p class="mt-3 text-4xl font-bold">{{ number_format($empresaActual->visitas ?? 0) }}</p>
                    <p class="mt-2 text-sm text-zinc-500">Cuenta las visitas al inicio publico y a las tablas de temporada.</p>
                    <button type="submit" form="formularioReiniciarVisitas" class="mt-4 inline-flex items-center gap-2 rounded-md border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50">
                        <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                        Reiniciar contador
                    </button>
                </section>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('panel.dashboard') }}" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-semibold transition">
                        <i data-lucide="x" class="h-4 w-4"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Guardar
                    </button>
                </div>
            </aside>
        </form>

        <form id="formularioReiniciarVisitas" method="POST" action="{{ route('gestion.empresa.reiniciar-visitas') }}">
            @csrf
        </form>
    </div>
</x-panel-layout>
