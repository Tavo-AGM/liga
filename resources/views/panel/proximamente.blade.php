<x-app-layout>
    <main class="app-fondo min-h-screen px-6 py-10 text-zinc-950">
        <section class="mx-auto max-w-3xl rounded-lg border border-zinc-200 bg-white p-8">
            <p class="text-sm font-medium uppercase tracking-[0.18em] texto-marca">Modulo protegido</p>
            <h1 class="mt-3 text-3xl font-bold">Vista en construccion</h1>
            <p class="mt-4 text-zinc-600">Esta ruta ya esta protegida por sesion y queda preparada para conectar el CRUD correspondiente.</p>
            <a href="{{ route('panel.dashboard') }}" class="btn-primary mt-6 inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Volver al dashboard
            </a>
        </section>
    </main>
</x-app-layout>
