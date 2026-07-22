<?php

namespace App\Http\Controllers\Encargados;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorPanelEncargado extends Controller
{
    public function index(): View
    {
        $usuarioActual = Auth::user();
        $equiposAdministradosIds = $this->equiposAdministrados($usuarioActual->id);
        $equiposAsignados = DB::table('equipos')
            ->whereIn('id', $equiposAdministradosIds ?: [0])
            ->orderBy('nombre')
            ->get();
        $equipoAsignado = $equiposAsignados->first();
        $jugadores = collect();

        if (! empty($equiposAdministradosIds)) {
            $jugadores = DB::table('jugador_equipo_liga')
                ->join('personas', 'jugador_equipo_liga.persona_id', '=', 'personas.id')
                ->join('equipos', 'jugador_equipo_liga.equipo_id', '=', 'equipos.id')
                ->leftJoin('ligas', 'jugador_equipo_liga.liga_id', '=', 'ligas.id')
                ->leftJoin('temporadas', 'jugador_equipo_liga.temporada_id', '=', 'temporadas.id')
                ->whereIn('jugador_equipo_liga.equipo_id', $equiposAdministradosIds)
                ->where('personas.tipo_persona', 'jugador')
                ->where('jugador_equipo_liga.estado', 'activo')
                ->select(
                    'personas.id',
                    'personas.nombre',
                    'personas.apellido_paterno',
                    'personas.apellido_materno',
                    'personas.fecha_nacimiento',
                    'personas.estado',
                    'equipos.nombre as equipo_nombre',
                    'ligas.nombre as liga_nombre',
                    'temporadas.nombre as temporada_nombre'
                )
                ->orderBy('equipos.nombre')
                ->orderBy('personas.nombre')
                ->get();
        }

        $jugadoresDisponibles = DB::table('personas')
            ->where('tipo_persona', 'jugador')
            ->where('estado', true)
            ->select('id', DB::raw("trim(concat(nombre, ' ', coalesce(apellido_paterno, ''), ' ', coalesce(apellido_materno, ''))) as nombre_completo"))
            ->orderBy('nombre')
            ->get();
        $ligasDisponibles = DB::table('ligas')->orderBy('nombre')->get();
        $temporadasDisponibles = DB::table('temporadas')->orderBy('nombre')->get();
        $traspasosRecibidos = $this->consultaTraspasos()
            ->whereIn('traspasos.equipo_origen_id', $equiposAdministradosIds ?: [0])
            ->where('traspasos.estado', 'pendiente')
            ->orderByDesc('traspasos.created_at')
            ->get();
        $traspasosEnviados = $this->consultaTraspasos()
            ->where('traspasos.usuario_solicitante_id', $usuarioActual->id)
            ->orderByDesc('traspasos.created_at')
            ->limit(8)
            ->get();

        $accionesEquipo = [
            'Editar informacion del equipo',
            'Ingresar jugadores',
            'Administrar plantilla',
            'Revisar traspasos',
        ];

        return view('encargados.panel', compact(
            'equipoAsignado',
            'equiposAsignados',
            'jugadores',
            'accionesEquipo',
            'jugadoresDisponibles',
            'ligasDisponibles',
            'temporadasDisponibles',
            'traspasosRecibidos',
            'traspasosEnviados'
        ));
    }

    private function equiposAdministrados(int $usuarioId): array
    {
        $usuarioActual = DB::table('usuarios')->where('id', $usuarioId)->first();
        $equipos = DB::table('usuario_equipo')
            ->where('usuario_id', $usuarioId)
            ->where('estado', true)
            ->pluck('equipo_id')
            ->toArray();

        if ($usuarioActual?->equipo_id) {
            $equipos[] = $usuarioActual->equipo_id;
        }

        return array_values(array_unique(array_map('intval', $equipos)));
    }

    private function consultaTraspasos()
    {
        return DB::table('traspasos')
            ->join('personas', 'traspasos.jugador_id', '=', 'personas.id')
            ->leftJoin('equipos as origen', 'traspasos.equipo_origen_id', '=', 'origen.id')
            ->join('equipos as destino', 'traspasos.equipo_destino_id', '=', 'destino.id')
            ->leftJoin('ligas as liga_origen', 'traspasos.liga_origen_id', '=', 'liga_origen.id')
            ->leftJoin('ligas as liga_destino', 'traspasos.liga_destino_id', '=', 'liga_destino.id')
            ->select(
                'traspasos.*',
                DB::raw("trim(concat(personas.nombre, ' ', coalesce(personas.apellido_paterno, ''), ' ', coalesce(personas.apellido_materno, ''))) as jugador_nombre"),
                'origen.nombre as equipo_origen_nombre',
                'destino.nombre as equipo_destino_nombre',
                'liga_origen.nombre as liga_origen_nombre',
                'liga_destino.nombre as liga_destino_nombre'
            );
    }
}
