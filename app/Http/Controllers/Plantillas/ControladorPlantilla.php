<?php

namespace App\Http\Controllers\Plantillas;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorPlantilla extends Controller
{
    public function index(): View
    {
        $registros = DB::table('jugador_equipo_liga')
            ->join('personas', 'jugador_equipo_liga.persona_id', '=', 'personas.id')
            ->join('equipos', 'jugador_equipo_liga.equipo_id', '=', 'equipos.id')
            ->leftJoin('ligas', 'jugador_equipo_liga.liga_id', '=', 'ligas.id')
            ->leftJoin('temporadas', 'jugador_equipo_liga.temporada_id', '=', 'temporadas.id')
            ->select(
                'jugador_equipo_liga.id',
                'jugador_equipo_liga.fecha_alta',
                'jugador_equipo_liga.fecha_baja',
                'jugador_equipo_liga.estado',
                'jugador_equipo_liga.origen',
                DB::raw("trim(concat(personas.nombre, ' ', coalesce(personas.apellido_paterno, ''), ' ', coalesce(personas.apellido_materno, ''))) as jugador_nombre"),
                'equipos.nombre as equipo_nombre',
                'ligas.nombre as liga_nombre',
                'temporadas.nombre as temporada_nombre'
            )
            ->orderBy('personas.nombre')
            ->paginate(10);

        return view('panel.crud.index', [
            'tituloModulo' => 'Plantillas',
            'descripcionModulo' => 'Vincula jugadores con equipos por liga y temporada.',
            'registros' => $registros,
            'columnas' => [
                ['campo' => 'jugador_nombre', 'etiqueta' => 'Jugador'],
                ['campo' => 'equipo_nombre', 'etiqueta' => 'Equipo'],
                ['campo' => 'liga_nombre', 'etiqueta' => 'Liga'],
                ['campo' => 'temporada_nombre', 'etiqueta' => 'Temporada'],
                ['campo' => 'estado', 'etiqueta' => 'Estado'],
                ['campo' => 'origen', 'etiqueta' => 'Origen'],
            ],
            'rutaCrear' => route('gestion.plantillas.create'),
            'rutaEditarNombre' => 'gestion.plantillas.edit',
            'rutaEliminarNombre' => 'gestion.plantillas.destroy',
        ]);
    }

    public function create(): View
    {
        return $this->formulario('Agregar jugador a plantilla', route('gestion.plantillas.store'));
    }

    public function store(Request $solicitud): RedirectResponse
    {
        $datosValidados = $this->datosValidados($solicitud);

        DB::table('jugador_equipo_liga')->insert($datosValidados + [
            'origen' => $datosValidados['origen'] ?? 'registro',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('gestion.plantillas.index')->with('mensaje', 'Plantilla actualizada correctamente.');
    }

    public function edit(int $plantilla): View
    {
        $registro = DB::table('jugador_equipo_liga')->where('id', $plantilla)->first();
        abort_if(! $registro, 404);

        return $this->formulario('Editar plantilla', route('gestion.plantillas.update', $plantilla), $registro, 'PUT');
    }

    public function update(Request $solicitud, int $plantilla): RedirectResponse
    {
        $registro = DB::table('jugador_equipo_liga')->where('id', $plantilla)->first();
        abort_if(! $registro, 404);

        DB::table('jugador_equipo_liga')->where('id', $plantilla)->update($this->datosValidados($solicitud) + [
            'updated_at' => now(),
        ]);

        return redirect()->route('gestion.plantillas.index')->with('mensaje', 'Plantilla actualizada correctamente.');
    }

    public function destroy(int $plantilla): RedirectResponse
    {
        DB::table('jugador_equipo_liga')->where('id', $plantilla)->delete();

        return redirect()->route('gestion.plantillas.index')->with('mensaje', 'Registro de plantilla eliminado correctamente.');
    }

    private function datosValidados(Request $solicitud): array
    {
        return $solicitud->validate([
            'persona_id' => ['required', 'exists:personas,id'],
            'equipo_id' => ['required', 'exists:equipos,id'],
            'liga_id' => ['nullable', 'exists:ligas,id'],
            'temporada_id' => ['nullable', 'exists:temporadas,id'],
            'fecha_alta' => ['nullable', 'date'],
            'fecha_baja' => ['nullable', 'date'],
            'estado' => ['required', 'in:activo,inactivo'],
            'origen' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function formulario(string $tituloFormulario, string $accionFormulario, object $registro = null, string $metodoFormulario = 'POST'): View
    {
        $jugadores = DB::table('personas')
            ->where('tipo_persona', 'jugador')
            ->select('id', DB::raw("trim(concat(nombre, ' ', coalesce(apellido_paterno, ''), ' ', coalesce(apellido_materno, ''))) as nombre_completo"))
            ->orderBy('nombre')
            ->pluck('nombre_completo', 'id')
            ->toArray();
        $equipos = DB::table('equipos')->orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $ligas = DB::table('ligas')->orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $temporadas = DB::table('temporadas')
            ->leftJoin('ligas', 'temporadas.liga_id', '=', 'ligas.id')
            ->select('temporadas.id', DB::raw("concat(coalesce(ligas.nombre, 'Liga sin nombre'), ' / ', temporadas.nombre) as nombre_completo"))
            ->orderBy('ligas.nombre')
            ->orderBy('temporadas.nombre')
            ->pluck('nombre_completo', 'temporadas.id')
            ->toArray();

        return view('panel.crud.formulario', [
            'tituloFormulario' => $tituloFormulario,
            'accionFormulario' => $accionFormulario,
            'metodoFormulario' => $metodoFormulario,
            'rutaRegreso' => route('gestion.plantillas.index'),
            'registro' => $registro,
            'campos' => [
                ['nombre' => 'persona_id', 'etiqueta' => 'Jugador', 'tipo' => 'select', 'opciones' => $jugadores, 'requerido' => true],
                ['nombre' => 'equipo_id', 'etiqueta' => 'Equipo', 'tipo' => 'select', 'opciones' => $equipos, 'requerido' => true],
                ['nombre' => 'liga_id', 'etiqueta' => 'Liga', 'tipo' => 'select', 'opciones' => $ligas],
                ['nombre' => 'temporada_id', 'etiqueta' => 'Temporada', 'tipo' => 'select', 'opciones' => $temporadas],
                ['nombre' => 'fecha_alta', 'etiqueta' => 'Fecha de alta', 'tipo' => 'date'],
                ['nombre' => 'fecha_baja', 'etiqueta' => 'Fecha de baja', 'tipo' => 'date'],
                ['nombre' => 'estado', 'etiqueta' => 'Estado', 'tipo' => 'select', 'opciones' => ['activo' => 'Activo', 'inactivo' => 'Inactivo'], 'valorDefecto' => 'activo', 'requerido' => true],
                ['nombre' => 'origen', 'etiqueta' => 'Origen', 'tipo' => 'text', 'valorDefecto' => 'registro'],
            ],
        ]);
    }
}
