<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorInicio extends Controller
{
    public function index(): View
    {
        $this->registrarVisita();
        $fechaActual = Carbon::today();

        $temporadasActuales = DB::table('temporadas')
            ->join('ligas', 'temporadas.liga_id', '=', 'ligas.id')
            ->select(
                'temporadas.id',
                'temporadas.nombre',
                'temporadas.desde',
                'temporadas.hasta',
                'ligas.nombre as liga_nombre',
                'ligas.eslogan'
            )
            ->where(function ($consulta) use ($fechaActual) {
                $consulta
                    ->whereNull('temporadas.desde')
                    ->orWhereDate('temporadas.desde', '<=', $fechaActual);
            })
            ->where(function ($consulta) use ($fechaActual) {
                $consulta
                    ->whereNull('temporadas.hasta')
                    ->orWhereDate('temporadas.hasta', '>=', $fechaActual);
            })
            ->orderBy('ligas.nombre')
            ->orderBy('temporadas.nombre')
            ->get();

        return view('publico.index', compact('temporadasActuales'));
    }

    public function tablaTemporada(int $temporada): View
    {
        $this->registrarVisita();
        $temporadaActual = DB::table('temporadas')
            ->join('ligas', 'temporadas.liga_id', '=', 'ligas.id')
            ->select(
                'temporadas.id',
                'temporadas.nombre',
                'temporadas.desde',
                'temporadas.hasta',
                'ligas.nombre as liga_nombre',
                'ligas.eslogan'
            )
            ->where('temporadas.id', $temporada)
            ->first();

        abort_if(! $temporadaActual, 404);

        $tablaEquipos = $this->obtenerTablaEquipos($temporada);
        $lideresGoleadores = $this->obtenerLideresEventos($temporada, ['gol', 'goles', 'goal']);
        $lideresAsistidores = $this->obtenerLideresEventos($temporada, ['asistencia', 'asistencias', 'assist']);
        $proximosPartidos = $this->obtenerProximosPartidos($temporada);

        return view('publico.tabla', compact('temporadaActual', 'tablaEquipos', 'lideresGoleadores', 'lideresAsistidores', 'proximosPartidos'));
    }

    private function obtenerTablaEquipos(int $temporada): array
    {
        $equipos = [];

        $equipoIds = DB::table('partidos')
            ->where('temporada_id', $temporada)
            ->where(function ($consulta) {
                $consulta->whereNotNull('equipo_local')->orWhereNotNull('equipo_visitante');
            })
            ->get(['equipo_local', 'equipo_visitante'])
            ->flatMap(fn ($partido) => [$partido->equipo_local, $partido->equipo_visitante])
            ->filter()
            ->merge(
                DB::table('torneo_equipo')
                    ->join('torneos', 'torneo_equipo.torneo_id', '=', 'torneos.id')
                    ->where('torneos.temporada_id', $temporada)
                    ->pluck('torneo_equipo.equipo_id')
            )
            ->unique()
            ->values();

        if ($equipoIds->isEmpty()) {
            return [];
        }

        $equiposRegistrados = DB::table('equipos')
            ->whereIn('id', $equipoIds)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'imagen']);

        foreach ($equiposRegistrados as $equipo) {
            $this->prepararEquipo($equipos, $equipo->id);
            $equipos[$equipo->id]['nombre'] = $equipo->nombre;
            $equipos[$equipo->id]['imagen'] = $equipo->imagen;
        }

        $partidosJugados = DB::table('partidos')
            ->where('temporada_id', $temporada)
            ->where('estado', 'jugado')
            ->whereNotNull('goles_local')
            ->whereNotNull('goles_visitante')
            ->get();

        foreach ($partidosJugados as $partido) {
            if (! isset($equipos[$partido->equipo_local]) || ! isset($equipos[$partido->equipo_visitante])) {
                continue;
            }

            $this->sumarPartido($equipos[$partido->equipo_local], (int) $partido->goles_local, (int) $partido->goles_visitante);
            $this->sumarPartido($equipos[$partido->equipo_visitante], (int) $partido->goles_visitante, (int) $partido->goles_local);
        }

        foreach ($equipos as $equipoId => $estadisticaEquipo) {
            $equipos[$equipoId]['diferencia'] = $estadisticaEquipo['golesFavor'] - $estadisticaEquipo['golesContra'];
        }

        usort($equipos, function ($equipoA, $equipoB) {
            return [$equipoB['puntos'], $equipoB['diferencia'], $equipoB['golesFavor'], $equipoA['nombre']]
                <=> [$equipoA['puntos'], $equipoA['diferencia'], $equipoA['golesFavor'], $equipoB['nombre']];
        });

        return $equipos;
    }

    private function registrarVisita(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('empresa')) {
            return;
        }

        $empresaActual = DB::table('empresa')->orderBy('id')->first();

        if (! $empresaActual) {
            DB::table('empresa')->insert([
                'nombre' => 'Liga Local',
                'texto_pie_pagina' => 'Sistema de gestion deportiva para ligas locales.',
                'visitas' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('empresa')->where('id', $empresaActual->id)->increment('visitas');
    }

    private function prepararEquipo(array &$equipos, int $equipoId): void
    {
        $equipos[$equipoId] ??= [
            'equipoId' => $equipoId,
            'nombre' => '',
            'imagen' => null,
            'jugados' => 0,
            'ganados' => 0,
            'empatados' => 0,
            'perdidos' => 0,
            'golesFavor' => 0,
            'golesContra' => 0,
            'diferencia' => 0,
            'puntos' => 0,
        ];
    }

    private function sumarPartido(array &$equipo, int $golesFavor, int $golesContra): void
    {
        $equipo['jugados']++;
        $equipo['golesFavor'] += $golesFavor;
        $equipo['golesContra'] += $golesContra;

        if ($golesFavor > $golesContra) {
            $equipo['ganados']++;
            $equipo['puntos'] += 3;
            return;
        }

        if ($golesFavor === $golesContra) {
            $equipo['empatados']++;
            $equipo['puntos']++;
            return;
        }

        $equipo['perdidos']++;
    }

    private function obtenerLideresEventos(int $temporada, array $tiposEvento)
    {
        return DB::table('partido_eventos')
            ->join('partidos', 'partido_eventos.partido_id', '=', 'partidos.id')
            ->join('personas', 'partido_eventos.persona_id', '=', 'personas.id')
            ->where('partidos.temporada_id', $temporada)
            ->whereIn(DB::raw('lower(partido_eventos.nombre)'), $tiposEvento)
            ->select(
                'personas.id',
                'personas.nombre',
                'personas.apellido_paterno',
                'personas.apellido_materno',
                DB::raw('count(*) as total')
            )
            ->groupBy('personas.id', 'personas.nombre', 'personas.apellido_paterno', 'personas.apellido_materno')
            ->orderByDesc('total')
            ->orderBy('personas.nombre')
            ->limit(5)
            ->get();
    }

    private function obtenerProximosPartidos(int $temporada)
    {
        return DB::table('partidos')
            ->leftJoin('equipos as equipos_locales', 'partidos.equipo_local', '=', 'equipos_locales.id')
            ->leftJoin('equipos as equipos_visitantes', 'partidos.equipo_visitante', '=', 'equipos_visitantes.id')
            ->leftJoin('campos', 'partidos.campo_id', '=', 'campos.id')
            ->leftJoin('torneos', 'partidos.torneo_id', '=', 'torneos.id')
            ->select(
                'partidos.fecha',
                'partidos.hora',
                'partidos.jornada',
                'partidos.fase',
                'equipos_locales.nombre as equipo_local_nombre',
                'equipos_visitantes.nombre as equipo_visitante_nombre',
                'campos.nombre as campo_nombre',
                'torneos.nombre as torneo_nombre'
            )
            ->where('partidos.temporada_id', $temporada)
            ->whereIn('partidos.estado', ['por_jugar', 'aplazado'])
            ->where(function ($consulta) {
                $consulta->whereNull('partidos.fecha')
                    ->orWhereDate('partidos.fecha', '>=', now()->toDateString());
            })
            ->orderByRaw('partidos.fecha is null')
            ->orderBy('partidos.fecha')
            ->orderBy('partidos.hora')
            ->limit(5)
            ->get();
    }
}
