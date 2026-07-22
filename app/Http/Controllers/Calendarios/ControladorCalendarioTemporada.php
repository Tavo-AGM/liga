<?php

namespace App\Http\Controllers\Calendarios;

use App\Http\Controllers\Controller;
use App\Support\GeneradorPartidos;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class ControladorCalendarioTemporada extends Controller
{
    public function create(): View
    {
        return view('calendarios.temporada', $this->datosFormulario());
    }

    public function store(Request $solicitud): RedirectResponse
    {
        $datosValidados = $solicitud->validate([
            'temporada_id' => ['required', 'exists:temporadas,id'],
            'equipos' => ['required', 'array', 'min:2'],
            'equipos.*' => ['exists:equipos,id'],
            'ida_vuelta' => ['required', 'boolean'],
            'dias_juego' => ['required', 'array', 'min:1'],
            'dias_juego.*' => ['in:0,1,2,3,4,5,6'],
            'horarios' => ['required', 'string'],
            'arbitros' => ['nullable', 'array'],
            'arbitros.*' => ['exists:personas,id'],
            'tolerancia_horas' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        $temporada = DB::table('temporadas')
            ->leftJoin('ligas', 'temporadas.liga_id', '=', 'ligas.id')
            ->select('temporadas.*', 'ligas.id as liga_id')
            ->where('temporadas.id', $datosValidados['temporada_id'])
            ->first();

        $equipos = DB::table('equipos')
            ->whereIn('id', $datosValidados['equipos'])
            ->orderBy('nombre')
            ->get();

        $horarios = $this->normalizarHorarios($datosValidados['horarios']);

        try {
            $partidosBase = GeneradorPartidos::generarRoundRobin($equipos, (bool) $datosValidados['ida_vuelta']);
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

        foreach ($partidosProgramados as $partido) {
            DB::table('partidos')->insert([
                'fecha' => $partido['fecha'],
                'hora' => $partido['hora'],
                'estado' => 'por_jugar',
                'campo_id' => $partido['campo_id'],
                'liga_id' => $temporada->liga_id,
                'temporada_id' => $temporada->id,
                'equipo_local' => $partido['equipo_local']->id,
                'equipo_visitante' => $partido['equipo_visitante']->id,
                'arbitro' => $partido['arbitro'],
                'jornada' => $partido['jornada'],
                'fase' => $partido['fase'],
                'grupo' => $partido['grupo'],
                'metadatos' => json_encode(['origen' => 'generador_temporada']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('gestion.calendarios.temporada.create')
            ->with('mensaje', 'Se generaron '.count($partidosProgramados).' partidos para la temporada.');
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
            'arbitros' => DB::table('personas')
                ->where('tipo_persona', 'arbitro')
                ->orderBy('nombre')
                ->get(),
            'diasSemana' => $this->diasSemana(),
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
