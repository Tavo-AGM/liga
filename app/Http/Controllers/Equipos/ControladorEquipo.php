<?php

namespace App\Http\Controllers\Equipos;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Support\GestorImagenes;

class ControladorEquipo extends Controller
{
    public function index(): View
    {
        $registros = DB::table('equipos')
            ->leftJoin('campos', 'equipos.campo_id', '=', 'campos.id')
            ->select('equipos.id', 'equipos.nombre', 'equipos.imagen', 'equipos.descripcion', 'campos.nombre as campo_nombre')
            ->orderBy('equipos.nombre')
            ->paginate(10);

        $registros->getCollection()->transform(function ($equipo) {
            $equipo->ligas_vinculadas = DB::table('equipo_liga')
                ->join('ligas', 'equipo_liga.liga_id', '=', 'ligas.id')
                ->where('equipo_liga.equipo_id', $equipo->id)
                ->orderBy('ligas.nombre')
                ->pluck('ligas.nombre')
                ->implode(', ');

            return $equipo;
        });

        return view('panel.crud.index', [
            'tituloModulo' => 'Equipos',
            'descripcionModulo' => 'Gestiona equipos participantes.',
            'registros' => $registros,
            'columnas' => [
                ['campo' => 'nombre', 'etiqueta' => 'Nombre'],
                ['campo' => 'imagen', 'etiqueta' => 'Imagen', 'tipo' => 'imagen'],
                ['campo' => 'campo_nombre', 'etiqueta' => 'Campo'],
                ['campo' => 'ligas_vinculadas', 'etiqueta' => 'Ligas'],
                ['campo' => 'descripcion', 'etiqueta' => 'Descripcion'],
            ],
            'rutaCrear' => route('gestion.equipos.create'),
            'rutaEditarNombre' => 'gestion.equipos.edit',
            'rutaEliminarNombre' => 'gestion.equipos.destroy',
        ]);
    }

    public function create(): View
    {
        return $this->formulario('Crear equipo', route('gestion.equipos.store'));
    }

    public function store(Request $solicitud): RedirectResponse
    {
        $datosValidados = $this->datosValidados($solicitud);
        $ligasVinculadas = $datosValidados['ligas'] ?? [];
        unset($datosValidados['ligas']);

        $datosValidados['imagen'] = GestorImagenes::guardar($solicitud, 'equipos');

        $equipoId = DB::table('equipos')->insertGetId($datosValidados + ['created_at' => now(), 'updated_at' => now()]);
        $this->sincronizarLigas($equipoId, $ligasVinculadas);

        return redirect()->route('gestion.equipos.index')->with('mensaje', 'Equipo creado correctamente.');
    }

    public function edit(int $equipo): View
    {
        $registro = DB::table('equipos')->where('id', $equipo)->first();
        abort_if(! $registro, 404);

        return $this->formulario('Editar equipo', route('gestion.equipos.update', $equipo), $registro, 'PUT');
    }

    public function update(Request $solicitud, int $equipo): RedirectResponse
    {
        $equipoActual = DB::table('equipos')->where('id', $equipo)->first();
        abort_if(! $equipoActual, 404);

        $datosValidados = $this->datosValidados($solicitud);
        $ligasVinculadas = $datosValidados['ligas'] ?? [];
        unset($datosValidados['ligas']);

        $datosValidados['imagen'] = GestorImagenes::guardar($solicitud, 'equipos', $equipoActual->imagen);

        DB::table('equipos')->where('id', $equipo)->update($datosValidados + ['updated_at' => now()]);
        $this->sincronizarLigas($equipo, $ligasVinculadas);

        return redirect()->route('gestion.equipos.index')->with('mensaje', 'Equipo actualizado correctamente.');
    }

    public function destroy(int $equipo): RedirectResponse
    {
        $equipoActual = DB::table('equipos')->where('id', $equipo)->first();
        GestorImagenes::eliminar($equipoActual?->imagen);

        DB::table('equipos')->where('id', $equipo)->delete();

        return redirect()->route('gestion.equipos.index')->with('mensaje', 'Equipo eliminado correctamente.');
    }

    private function datosValidados(Request $solicitud): array
    {
        return $solicitud->validate([
            'campo_id' => ['nullable', 'exists:campos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'imagen' => GestorImagenes::reglas(),
            'descripcion' => ['nullable', 'string'],
            'ligas' => ['nullable', 'array'],
            'ligas.*' => ['exists:ligas,id'],
        ]);
    }

    private function formulario(string $tituloFormulario, string $accionFormulario, object $registro = null, string $metodoFormulario = 'POST'): View
    {
        $camposDisponibles = DB::table('campos')->orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $ligasDisponibles = DB::table('ligas')->orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $ligasSeleccionadas = $registro
            ? DB::table('equipo_liga')->where('equipo_id', $registro->id)->pluck('liga_id')->map(fn ($ligaId) => (string) $ligaId)->toArray()
            : [];

        return view('panel.crud.formulario', [
            'tituloFormulario' => $tituloFormulario,
            'accionFormulario' => $accionFormulario,
            'metodoFormulario' => $metodoFormulario,
            'rutaRegreso' => route('gestion.equipos.index'),
            'registro' => $registro,
            'campos' => [
                ['nombre' => 'nombre', 'etiqueta' => 'Nombre', 'tipo' => 'text', 'requerido' => true],
                ['nombre' => 'campo_id', 'etiqueta' => 'Campo vinculado', 'tipo' => 'select', 'opciones' => $camposDisponibles],
                ['nombre' => 'ligas', 'etiqueta' => 'Ligas vinculadas', 'tipo' => 'multiselect', 'opciones' => $ligasDisponibles, 'valoresSeleccionados' => $ligasSeleccionadas],
                ['nombre' => 'imagen', 'etiqueta' => 'Imagen', 'tipo' => 'file'],
                ['nombre' => 'descripcion', 'etiqueta' => 'Descripcion', 'tipo' => 'textarea'],
            ],
        ]);
    }

    private function sincronizarLigas(int $equipoId, array $ligasVinculadas): void
    {
        DB::table('equipo_liga')->where('equipo_id', $equipoId)->delete();

        foreach (array_unique($ligasVinculadas) as $ligaId) {
            DB::table('equipo_liga')->insert([
                'equipo_id' => $equipoId,
                'liga_id' => $ligaId,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
