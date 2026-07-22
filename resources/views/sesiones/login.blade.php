<x-app-layout>
    @php
        $empresaSistema = \App\Support\ConfiguracionEmpresa::obtener();
    @endphp

    <main class="min-h-screen grid lg:grid-cols-[1.05fr_.95fr]">
        <section class="app-bar hidden flex-col justify-between px-12 py-10 lg:flex">
            <div class="flex items-center gap-3 text-lg font-semibold tracking-wide">
                @if ($empresaSistema->imagen)
                    <img src="{{ $empresaSistema->imagen }}" alt="Icono {{ $empresaSistema->nombre }}" class="h-10 w-10 rounded-md bg-white object-cover">
                @endif
                {{ $empresaSistema->nombre }}
            </div>
            <div class="max-w-xl">
                <p class="text-sm uppercase tracking-[0.25em] opacity-80">{{ $empresaSistema->eslogan ?: 'Gestion deportiva' }}</p>
                <h1 class="mt-5 text-5xl font-bold leading-tight">Administra ligas, torneos y partidos desde un solo panel.</h1>
                <p class="mt-6 text-lg opacity-90">{{ $empresaSistema->descripcion ?: 'Prototipo inicial para organizar equipos, jugadores, campos, arbitros, calendarios y estadisticas publicas.' }}</p>
            </div>
            <p class="text-sm opacity-80">Primer acceso: admin@liga.local / admin</p>
        </section>

        <section class="flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <p class="text-sm uppercase tracking-[0.25em] texto-acento">{{ $empresaSistema->nombre }}</p>
                    <h1 class="mt-3 text-3xl font-bold">Iniciar sesion</h1>
                </div>

                <div class="rounded-lg border border-zinc-800 bg-zinc-900/80 p-8 shadow-2xl shadow-black/30">
                    <h2 class="text-2xl font-semibold">Bienvenido</h2>
                    <p class="mt-2 text-sm text-zinc-400">Ingresa con el usuario administrador para entrar al dashboard.</p>

                    @if ($errors->any())
                        <div class="mt-5 rounded-md border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('sesiones.iniciar') }}" class="mt-7 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-zinc-200">Correo</label>
                            <input id="email" name="email" type="email" value="{{ old('email', 'admin@liga.local') }}" required autofocus class="mt-2 w-full rounded-md border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-zinc-100 outline-none transition focus:border-[var(--color-acento)] focus:ring-2 focus:ring-[var(--color-acento)]/20">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-zinc-200">Contrasena</label>
                            <input id="password" name="password" type="password" value="admin" required class="mt-2 w-full rounded-md border border-zinc-700 bg-zinc-950 px-3 py-2.5 text-zinc-100 outline-none transition focus:border-[var(--color-acento)] focus:ring-2 focus:ring-[var(--color-acento)]/20">
                        </div>

                        <label class="flex items-center gap-3 text-sm text-zinc-300">
                            <input name="recordarSesion" type="checkbox" class="h-4 w-4 rounded border-zinc-700 bg-zinc-950 text-[var(--color-acento)] focus:ring-[var(--color-acento)]">
                            Recordar sesion
                        </label>

                        <button type="submit" class="btn-primary inline-flex w-full items-center justify-center gap-2 rounded-md px-4 py-2.5 font-semibold transition focus:outline-none focus:ring-2 focus:ring-[var(--color-acento)]">
                            <i data-lucide="log-in" class="h-5 w-5"></i>
                            Entrar al panel
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</x-app-layout>
