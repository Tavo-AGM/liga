<?php

namespace App\Http\Controllers\Partidos;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ControladorPartido extends Controller
{
    public function index(): View
    {
        $partidos = DB::table('partidos')
            ->leftJoin('temporadas', 'partidos.temporada_id', '=', 'temporadas.id')
            ->leftJoin('torneos', 'partidos.torneo_id', '=', 'torneos.id')
            ->leftJoin('equipos as equipos_locales', 'partidos.equipo_local', '=', 'equipos_locales.id')
            ->leftJoin('equipos as equipos_visitantes', 'partidos.equipo_visitante', '=', 'equipos_visitantes.id')
            ->leftJoin('campos', 'partidos.campo_id', '=', 'campos.id')
            ->select(
                'partidos.id',
                'partidos.fecha',
                'partidos.hora',
                'partidos.estado',
                'partidos.jornada',
                'temporadas.nombre as temporada_nombre',
                'torneos.nombre as torneo_nombre',
                'equipos_locales.nombre as equipo_local_nombre',
                'equipos_visitantes.nombre as equipo_visitante_nombre',
                'campos.nombre as campo_nombre'
            )
            ->orderByRaw('partidos.fecha is null')
            ->orderBy('partidos.fecha')
            ->orderBy('partidos.hora')
            ->paginate(15);

        return view('partidos.index', compact('partidos'));
    }

    public function edit(int $partido): View
    {
        $partidoActual = DB::table('partidos')
            ->leftJoin('equipos as equipos_locales', 'partidos.equipo_local', '=', 'equipos_locales.id')
            ->leftJoin('equipos as equipos_visitantes', 'partidos.equipo_visitante', '=', 'equipos_visitantes.id')
            ->select('partidos.*', 'equipos_locales.nombre as equipo_local_nombre', 'equipos_visitantes.nombre as equipo_visitante_nombre')
            ->where('partidos.id', $partido)
            ->first();

        abort_if(! $partidoActual, 404);

        $campos = DB::table('campos')->orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $estados = $this->estados();

        return view('partidos.formulario', compact('partidoActual', 'campos', 'estados'));
    }

    public function update(Request $solicitud, int $partido): RedirectResponse
    {
        $datosValidados = $solicitud->validate([
            'fecha' => ['nullable', 'date'],
            'hora' => ['nullable', 'date_format:H:i'],
            'campo_id' => ['nullable', 'exists:campos,id'],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
        ]);

        DB::table('partidos')->where('id', $partido)->update($datosValidados + ['updated_at' => now()]);

        return redirect()->route('gestion.partidos.index')->with('mensaje', 'Partido actualizado correctamente.');
    }

    private function estados(): array
    {
        return [
            'por_jugar' => 'Por jugar',
            'aplazado' => 'Aplazado',
            'jugado' => 'Jugado',
        ];
    }
}
