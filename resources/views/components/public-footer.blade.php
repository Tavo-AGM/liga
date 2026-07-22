@php
    $empresaSistema = \App\Support\ConfiguracionEmpresa::obtener();
@endphp

<footer class="app-footer border-t border-zinc-200">
    <div class="texto-superficie mx-auto flex max-w-7xl flex-col gap-4 px-6 py-6 text-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="max-w-2xl">
            <p class="font-semibold">{{ $empresaSistema->nombre }}</p>
            <p class="mt-1">{{ $empresaSistema->texto_pie_pagina ?: 'Sistema de gestion deportiva para ligas locales.' }}</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-md border border-zinc-200 bg-white/40 px-3 py-2 font-semibold">
            <i data-lucide="eye" class="h-4 w-4 texto-marca"></i>
            {{ number_format($empresaSistema->visitas ?? 0) }} visitas
        </div>
    </div>
</footer>
