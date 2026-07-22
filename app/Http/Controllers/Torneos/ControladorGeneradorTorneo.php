<?php

namespace App\Http\Controllers\Torneos;

use App\Http\Controllers\Controller;
use App\Support\GeneradorPartidos;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class ControladorGeneradorTorneo extends Controller
{
    public function create(): View
    {
        return view('torneos.generar', $this->datosFormulario());
    }

    public function store(Request $solicitud): RedirectResponse
    {
        $datosValidados = $solicitud->validate([
            'temporada_id' => ['required', 'exists:temporadas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:liga,mundial,champions,eliminacion_directa,personalizado'],
            'equipos' => ['required', 'array', 'min:2'],
            'equipos.*' => ['exists:equipos,id'],
            'cantidad_grupos' => ['nullable', 'integer', 'min:1', 'max:16'],
            'ida_vuelta' => ['required', 'boolean'],
            'tercer_lugar' => ['required', 'boolean'],
            'dias_juego' => ['required', 'array', 'min:1'],
            'dias_juego.*' => ['in:0,1,2,3,4,5,6'],
            'horarios' => ['required', 'string'],
            'arbitros' => ['nullable', 'array'],
            'arbitros.*' => ['exists:personas,id'],
            'tolerancia_horas' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        $temporada = DB::table('temporadas')->where('id', $datosValidados['temporada_id'])->first();
        $equipos = DB::table('equipos')->whereIn('id', $datosValidados['equipos'])->orderBy('nombre')->get();
        $horarios = $this->normalizarHorarios($datosValidados['horarios']);

        $reglas = [
            'tipo' => $datosValidados['tipo'],
            'ida_vuelta' => (bool) $datosValidados['ida_vuelta'],
            'tercer_lugar' => (bool) $datosValidados['tercer_lugar'],
            'cantidad_grupos' => $datosValidados['cantidad_grupos'] ?? null,
            'dias_juego' => $datosValidados['dias_juego'],
            'horarios' => $horarios,
            'tolerancia_horas' => (int) $datosValidados['tolerancia_horas'],
        ];

        try {
            $partidosBase = $this->generarPartidosPorTipo($equipos, $reglas);
            $partidosProgramados = GeneradorPartidos::asignarProgramacion(
                $partidosBase,
                Carbon::parse($temporada->desde ?: now()),
                $datosValidados['dias_juego'],
                $horarios,
                $datosValidados['arbitros'] ?? [],
                $datosValidados['tolerancia_horas'] * 60
            );
        } catch (RuntimeException $excepcion) {
            return back()->withInput()->withErrors(['generador' => $excepcion->getMessage()]);
        }

        $torneoId = DB::table('torneos')->insertGetId([
            'temporada_id' => $temporada->id,
            'nombre' => $datosValidados['nombre'],
            'tipo' => $datosValidados['tipo'],
            'reglas' => json_encode($reglas),
            'estado' => 'generado',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($equipos as $equipo) {
            DB::table('torneo_equipo')->insert([
                'torneo_id' => $torneoId,
                'equipo_id' => $equipo->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($partidosProgramados as $partido) {
            DB::table('partidos')->insert([
                'fecha' => $partido['fecha'],
                'hora' => $partido['hora'],
                'estado' => 'por_jugar',
                'campo_id' => $partido['campo_id'],
                'liga_id' => $temporada->liga_id,
                'temporada_id' => $temporada->id,
                'torneo_id' => $torneoId,
                'equipo_local' => $partido['equipo_local']->id,
                'equipo_visitante' => $partido['equipo_visitante']->id,
                'arbitro' => $partido['arbitro'],
                'jornada' => $partido['jornada'],
                'fase' => $partido['fase'],
                'grupo' => $partido['grupo'],
                'metadatos' => json_encode(['origen' => 'generador_torneo', 'reglas' => $reglas]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('gestion.torneos.generar.create')
            ->with('mensaje', 'Torneo generado con '.count($partidosProgramados).' partidos.');
    }

    private function generarPartidosPorTipo($equipos, array $reglas): array
    {
        return match ($reglas['tipo']) {
            'liga' => GeneradorPartidos::generarRoundRobin($equipos, $reglas['ida_vuelta']),
            'mundial' => GeneradorPartidos::generarPorGrupos($equipos, (int) ($reglas['cantidad_grupos'] ?: max(1, ceil($equipos->count() / 4))), false),
            'champions' => GeneradorPartidos::generarRoundRobin($equipos, false),
            'eliminacion_directa' => GeneradorPartidos::generarEliminacionDirecta($equipos),
            default => $reglas['cantidad_grupos']
                ? GeneradorPartidos::generarPorGrupos($equipos, (int) $reglas['cantidad_grupos'], $reglas['ida_vuelta'])
                : GeneradorPartidos::generarRoundRobin($equipos, $reglas['ida_vuelta']),
        };
    }

    private function datosFormulario(): array
    {
        return [
            'temporadas' => DB::table('temporadas')
                ->leftJoin('ligas', 'temporadas.liga_id', '=', 'ligas.id')
                ->select('temporadas.id', 'temporadas.nombre', 'ligas.nombre as liga_nombre')
                ->orderByDesc('temporadas.desde')
                ->get(),
            'equipos' => DB::table('equipos')
                ->leftJoin('campos', 'equipos.campo_id', '=', 'campos.id')
                ->select('equipos.id', 'equipos.nombre', 'campos.nombre as campo_nombre')
                ->orderBy('equipos.nombre')
                ->get(),
            'arbitros' => DB::table('personas')->where('tipo_persona', 'arbitro')->orderBy('nombre')->get(),
            'diasSemana' => $this->diasSemana(),
            'tiposTorneo' => [
                'liga' => 'Liga / todos contra todos',
                'mundial' => 'Tipo Mundial / grupos y eliminatoria',
                'champions' => 'Tipo Champions / fase liga y eliminatoria',
                'eliminacion_directa' => 'Eliminacion directa',
                'personalizado' => 'Personalizado',
            ],
        ];
    }

    private function normalizarHorarios(string $horarios): array
    {
        return collect(preg_split('/[\s,]+/', $horarios))
            ->map(fn ($horario) => trim($horario))
            ->filter()
            ->map(fn ($horario) => preg_match('/^\d{2}:\d{2}$/', $horario) ? $horario : null)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function diasSemana(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            0 => 'Domingo',
        ];
    }
}
