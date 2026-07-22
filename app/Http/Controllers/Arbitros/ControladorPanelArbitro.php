<?php

namespace App\Http\Controllers\Arbitros;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorPanelArbitro extends Controller
{
    public function index(): View
    {
        $usuarioActual = Auth::user();

        $proximosPartidos = collect();

        if ($usuarioActual?->persona_id) {
            $proximosPartidos = DB::table('partidos')
                ->leftJoin('equipos as equipos_locales', 'partidos.equipo_local', '=', 'equipos_locales.id')
                ->leftJoin('equipos as equipos_visitantes', 'partidos.equipo_visitante', '=', 'equipos_visitantes.id')
                ->leftJoin('campos', 'partidos.campo_id', '=', 'campos.id')
                ->leftJoin('ligas', 'partidos.liga_id', '=', 'ligas.id')
                ->select(
                    'partidos.id',
                    'partidos.fecha',
                    'partidos.hora',
                    'partidos.estado',
                    'equipos_locales.nombre as equipo_local_nombre',
                    'equipos_visitantes.nombre as equipo_visitante_nombre',
                    'campos.nombre as campo_nombre',
                    'ligas.nombre as liga_nombre'
                )
                ->where('partidos.arbitro', $usuarioActual->persona_id)
                ->whereIn('partidos.estado', ['por_jugar', 'aplazado'])
                ->where(function ($consulta) {
                    $consulta->whereNull('partidos.fecha')
                        ->orWhereDate('partidos.fecha', '>=', Carbon::today());
                })
                ->orderBy('partidos.fecha')
                ->orderBy('partidos.hora')
                ->limit(8)
                ->get();
        }

        $accionesEstadisticas = [
            'Iniciar juego',
            'Registrar gol',
            'Registrar asistencia',
            'Registrar tarjeta',
            'Cerrar partido',
        ];

        return view('arbitros.panel', compact('proximosPartidos', 'accionesEstadisticas'));
    }
}
