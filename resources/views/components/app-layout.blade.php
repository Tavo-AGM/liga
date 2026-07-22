@php
    $empresaSistema = \App\Support\ConfiguracionEmpresa::obtener();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo ?? $empresaSistema->nombre }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --color-primario: {{ $empresaSistema->color_primario }};
            --color-secundario: {{ $empresaSistema->color_secundario }};
            --color-acento: {{ $empresaSistema->color_acento }};
            --color-fondo: {{ $empresaSistema->color_fondo }};
            --color-texto: {{ $empresaSistema->color_texto }};
            --color-menu: {{ $empresaSistema->color_menu }};
            --color-pie-pagina: {{ $empresaSistema->color_pie_pagina }};
        }

        .app-fondo {
            background-color: var(--color-fondo);
        }

        .app-bar,
        .btn-primary {
            background-color: var(--color-primario);
            color: var(--color-texto);
        }

        .app-menu {
            background-color: var(--color-menu);
        }

        .app-footer {
            background-color: var(--color-pie-pagina);
        }

        .texto-superficie {
            color: var(--color-secundario);
        }

        .btn-primary:hover {
            filter: brightness(1.08);
        }

        .btn-secondary {
            border-color: var(--color-secundario);
            color: var(--color-secundario);
            background-color: #ffffff;
        }

        .btn-secondary:hover {
            background-color: color-mix(in srgb, var(--color-secundario) 8%, #ffffff);
        }

        .texto-marca {
            color: var(--color-primario);
        }

        .texto-acento {
            color: var(--color-acento);
        }

        .menu-activo {
            background-color: color-mix(in srgb, var(--color-acento) 14%, #ffffff);
            color: var(--color-primario);
        }

        .tabla-encabezado {
            background-color: var(--color-secundario);
            color: #ffffff;
        }
    </style>
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
    {{ $slot }}
    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }

        document.querySelectorAll('table[data-tabla-buscable]').forEach((tabla, indiceTabla) => {
            const cuerpoTabla = tabla.tBodies[0];
            if (! cuerpoTabla) {
                return;
            }

            const filasDatos = Array.from(cuerpoTabla.querySelectorAll('tr')).filter((fila) => ! fila.querySelector('td[colspan]'));
            const filaVacia = Array.from(cuerpoTabla.querySelectorAll('tr')).find((fila) => fila.querySelector('td[colspan]'));

            if (filasDatos.length === 0) {
                return;
            }

            const paginacionCliente = tabla.dataset.tablaPaginacion !== 'servidor';
            const filasPorPagina = parseInt(tabla.dataset.tablaFilas || '10', 10);
            const contenedorTabla = tabla.closest('[data-tabla-contenedor]') || tabla.parentElement;
            const bloqueTabla = contenedorTabla.parentElement;
            let paginaActual = 1;

            const herramientas = document.createElement('div');
            herramientas.className = 'flex flex-col gap-3 border-b border-zinc-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between';
            herramientas.innerHTML = `
                <label class="relative block w-full sm:max-w-xs">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"></i>
                    <input type="search" class="w-full rounded-md border border-zinc-300 py-2 pl-9 pr-3 text-sm outline-none transition focus:border-[var(--color-primario)] focus:ring-2 focus:ring-[var(--color-acento)]/20" placeholder="Buscar en la tabla">
                </label>
                <p class="text-sm text-zinc-500" data-tabla-info></p>
            `;

            const paginador = document.createElement('div');
            paginador.className = 'flex flex-col gap-3 border-t border-zinc-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between';
            paginador.innerHTML = `
                <p class="text-sm text-zinc-500" data-tabla-pagina-info></p>
                <div class="flex gap-2">
                    <button type="button" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-semibold transition" data-tabla-anterior>
                        <i data-lucide="chevron-left" class="h-4 w-4"></i>
                        Anterior
                    </button>
                    <button type="button" class="btn-secondary inline-flex items-center gap-2 rounded-md border px-3 py-2 text-sm font-semibold transition" data-tabla-siguiente>
                        Siguiente
                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    </button>
                </div>
            `;

            bloqueTabla.insertBefore(herramientas, contenedorTabla);

            if (paginacionCliente) {
                bloqueTabla.insertBefore(paginador, contenedorTabla.nextSibling);
            }

            const entradaBusqueda = herramientas.querySelector('input');
            const textoInfo = herramientas.querySelector('[data-tabla-info]');
            const textoPagina = paginador.querySelector('[data-tabla-pagina-info]');
            const botonAnterior = paginador.querySelector('[data-tabla-anterior]');
            const botonSiguiente = paginador.querySelector('[data-tabla-siguiente]');
            const normalizar = (texto) => texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            const renderizarTabla = () => {
                const busqueda = normalizar(entradaBusqueda.value.trim());
                const filasFiltradas = filasDatos.filter((fila) => normalizar(fila.innerText).includes(busqueda));
                const totalPaginas = Math.max(1, Math.ceil(filasFiltradas.length / filasPorPagina));

                if (paginaActual > totalPaginas) {
                    paginaActual = totalPaginas;
                }

                filasDatos.forEach((fila) => {
                    fila.hidden = true;
                });

                const filasVisibles = paginacionCliente
                    ? filasFiltradas.slice((paginaActual - 1) * filasPorPagina, paginaActual * filasPorPagina)
                    : filasFiltradas;

                filasVisibles.forEach((fila) => {
                    fila.hidden = false;
                });

                if (filaVacia) {
                    filaVacia.hidden = filasFiltradas.length > 0;
                    if (filasFiltradas.length === 0) {
                        filaVacia.querySelector('td').textContent = 'Sin coincidencias';
                    }
                }

                textoInfo.textContent = `${filasFiltradas.length} de ${filasDatos.length} registro(s)`;

                if (paginacionCliente) {
                    textoPagina.textContent = `Pagina ${paginaActual} de ${totalPaginas}`;
                    botonAnterior.disabled = paginaActual === 1;
                    botonSiguiente.disabled = paginaActual === totalPaginas;
                    botonAnterior.classList.toggle('opacity-50', botonAnterior.disabled);
                    botonSiguiente.classList.toggle('opacity-50', botonSiguiente.disabled);
                }
            };

            entradaBusqueda.addEventListener('input', () => {
                paginaActual = 1;
                renderizarTabla();
            });

            if (paginacionCliente) {
                botonAnterior.addEventListener('click', () => {
                    paginaActual = Math.max(1, paginaActual - 1);
                    renderizarTabla();
                });
                botonSiguiente.addEventListener('click', () => {
                    paginaActual += 1;
                    renderizarTabla();
                });
            }

            renderizarTabla();
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
