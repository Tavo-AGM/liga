<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GeneradorPartidos
{
    public static function generarRoundRobin(Collection $equipos, bool $idaVuelta): array
    {
        $equiposOrdenados = $equipos->values()->all();

        if (count($equiposOrdenados) % 2 !== 0) {
            $equiposOrdenados[] = null;
        }

        $totalEquipos = count($equiposOrdenados);
        $totalJornadas = $totalEquipos - 1;
        $partidos = [];

        for ($jornada = 1; $jornada <= $totalJornadas; $jornada++) {
            for ($indice = 0; $indice < $totalEquipos / 2; $indice++) {
                $equipoA = $equiposOrdenados[$indice];
                $equipoB = $equiposOrdenados[$totalEquipos - 1 - $indice];

                if (! $equipoA || ! $equipoB) {
                    continue;
                }

                $partidos[] = [
                    'jornada' => $jornada,
                    'equipo_local' => $jornada % 2 === 0 ? $equipoB : $equipoA,
                    'equipo_visitante' => $jornada % 2 === 0 ? $equipoA : $equipoB,
                    'fase' => 'liga',
                    'grupo' => null,
                ];
            }

            $equipoFijo = array_shift($equiposOrdenados);
            $ultimoEquipo = array_pop($equiposOrdenados);
            array_unshift($equiposOrdenados, $equipoFijo);
            array_splice($equiposOrdenados, 1, 0, [$ultimoEquipo]);
        }

        if (! $idaVuelta) {
            return $partidos;
        }

        $partidosVuelta = [];

        foreach ($partidos as $partido) {
            $partidosVuelta[] = [
                ...$partido,
                'jornada' => $partido['jornada'] + $totalJornadas,
                'equipo_local' => $partido['equipo_visitante'],
                'equipo_visitante' => $partido['equipo_local'],
            ];
        }

        return [...$partidos, ...$partidosVuelta];
    }

    public static function generarEliminacionDirecta(Collection $equipos): array
    {
        $equiposOrdenados = $equipos->values();
        $partidos = [];

        for ($indice = 0; $indice < $equiposOrdenados->count(); $indice += 2) {
            if (! isset($equiposOrdenados[$indice + 1])) {
                continue;
            }

            $partidos[] = [
                'jornada' => 1,
                'equipo_local' => $equiposOrdenados[$indice],
                'equipo_visitante' => $equiposOrdenados[$indice + 1],
                'fase' => 'eliminacion',
                'grupo' => null,
            ];
        }

        return $partidos;
    }

    public static function generarPorGrupos(Collection $equipos, int $cantidadGrupos, bool $idaVuelta): array
    {
        $cantidadGrupos = max(1, $cantidadGrupos);
        $grupos = array_fill(0, $cantidadGrupos, []);

        foreach ($equipos->values() as $indice => $equipo) {
            $grupos[$indice % $cantidadGrupos][] = $equipo;
        }

        $partidos = [];

        foreach ($grupos as $indiceGrupo => $equiposGrupo) {
            $nombreGrupo = 'Grupo '.chr(65 + $indiceGrupo);
            $partidosGrupo = self::generarRoundRobin(collect($equiposGrupo), $idaVuelta);

            foreach ($partidosGrupo as $partido) {
                $partidos[] = [
                    ...$partido,
                    'fase' => 'grupos',
                    'grupo' => $nombreGrupo,
                ];
            }
        }

        return $partidos;
    }

    public static function asignarProgramacion(
        array $partidos,
        Carbon $fechaInicio,
        array $diasJuego,
        array $horarios,
        array $arbitros,
        int $toleranciaMinutos
    ): array {
        if (empty($diasJuego) || empty($horarios)) {
            throw new RuntimeException('Debes seleccionar al menos un dia y un horario.');
        }

        $ocupacionCampos = self::ocupacionCamposExistente();
        $indiceArbitro = 0;
        $partidosProgramados = [];

        foreach ($partidos as $partido) {
            $campoId = $partido['equipo_local']->campo_id ?? null;
            $programacion = self::buscarSlotDisponible($fechaInicio, $diasJuego, $horarios, $campoId, $ocupacionCampos, $toleranciaMinutos);

            if (! $programacion) {
                throw new RuntimeException('No se encontro espacio suficiente para programar todos los partidos. Agrega mas dias u horarios.');
            }

            if ($campoId) {
                $ocupacionCampos[$campoId][] = $programacion['fechaHora'];
            }

            $partidosProgramados[] = [
                ...$partido,
                'fecha' => $programacion['fecha'],
                'hora' => $programacion['hora'],
                'campo_id' => $campoId,
                'arbitro' => self::siguienteArbitro($arbitros, $indiceArbitro),
            ];
        }

        return $partidosProgramados;
    }

    private static function buscarSlotDisponible(Carbon $fechaInicio, array $diasJuego, array $horarios, ?int $campoId, array $ocupacionCampos, int $toleranciaMinutos): ?array
    {
        $fechaActual = $fechaInicio->copy()->startOfDay();

        for ($diasRevisados = 0; $diasRevisados < 370; $diasRevisados++) {
            if (! in_array((string) $fechaActual->dayOfWeek, $diasJuego, true)) {
                $fechaActual->addDay();
                continue;
            }

            foreach ($horarios as $horario) {
                $fechaHora = Carbon::parse($fechaActual->toDateString().' '.$horario);

                if (! self::existeConflictoCampo($campoId, $fechaHora, $ocupacionCampos, $toleranciaMinutos)) {
                    return [
                        'fecha' => $fechaActual->toDateString(),
                        'hora' => $horario,
                        'fechaHora' => $fechaHora,
                    ];
                }
            }

            $fechaActual->addDay();
        }

        return null;
    }

    private static function existeConflictoCampo(?int $campoId, Carbon $fechaHora, array $ocupacionCampos, int $toleranciaMinutos): bool
    {
        if (! $campoId || empty($ocupacionCampos[$campoId])) {
            return false;
        }

        foreach ($ocupacionCampos[$campoId] as $fechaHoraOcupada) {
            if (abs($fechaHora->diffInMinutes($fechaHoraOcupada, false)) < $toleranciaMinutos) {
                return true;
            }
        }

        return false;
    }

    private static function ocupacionCamposExistente(): array
    {
        $ocupacion = [];

        $partidosExistentes = DB::table('partidos')
            ->whereNotNull('campo_id')
            ->whereNotNull('fecha')
            ->whereNotNull('hora')
            ->get(['campo_id', 'fecha', 'hora']);

        foreach ($partidosExistentes as $partido) {
            $ocupacion[$partido->campo_id][] = Carbon::parse($partido->fecha.' '.$partido->hora);
        }

        return $ocupacion;
    }

    private static function siguienteArbitro(array $arbitros, int &$indiceArbitro): ?int
    {
        if (empty($arbitros)) {
            return null;
        }

        $arbitroId = $arbitros[$indiceArbitro % count($arbitros)];
        $indiceArbitro++;

        return (int) $arbitroId;
    }
}
