<?php

namespace App\Http\Controllers\Traspasos;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorTraspaso extends Controller
{
    public function index(): View
    {
        $traspasos = $this->consultaTraspasos()
            ->orderByDesc('traspasos.created_at')
            ->paginate(10);

        return view('traspasos.index', compact('traspasos'));
    }

    public function store(Request $solicitud): RedirectResponse
    {
        $usuarioActual = Auth::user();
        $equiposAdministrados = $this->equiposAdministrados($usuarioActual->id);

        $datosValidados = $solicitud->validate([
            'jugador_id' => ['required', 'exists:personas,id'],
            'equipo_destino_id' => ['required', 'exists:equipos,id'],
            'liga_destino_id' => ['nullable', 'exists:ligas,id'],
            'temporada_id' => ['nullable', 'exists:temporadas,id'],
            'mensaje' => ['nullable', 'string'],
        ]);

        abort_if(! in_array((int) $datosValidados['equipo_destino_id'], $equiposAdministrados, true), 403);

        $plantillaOrigen = DB::table('jugador_equipo_liga')
            ->where('persona_id', $datosValidados['jugador_id'])
            ->where('estado', 'activo')
            ->where('equipo_id', '!=', $datosValidados['equipo_destino_id'])
            ->orderByDesc('id')
            ->first();

        $usuarioReceptorId = $plantillaOrigen
            ? $this->primerUsuarioDeEquipo($plantillaOrigen->equipo_id, $usuarioActual->id)
            : null;

        DB::table('traspasos')->insert([
            'jugador_id' => $datosValidados['jugador_id'],
            'equipo_origen_id' => $plantillaOrigen?->equipo_id,
            'liga_origen_id' => $plantillaOrigen?->liga_id,
            'equipo_destino_id' => $datosValidados['equipo_destino_id'],
            'liga_destino_id' => $datosValidados['liga_destino_id'] ?? null,
            'temporada_id' => $datosValidados['temporada_id'] ?? null,
            'usuario_solicitante_id' => $usuarioActual->id,
            'usuario_receptor_id' => $usuarioReceptorId,
            'estado' => $plantillaOrigen ? 'pendiente' : 'aprobado',
            'mensaje' => $datosValidados['mensaje'] ?? null,
            'respondido_en' => $plantillaOrigen ? null : now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! $plantillaOrigen) {
            $this->registrarPlantilla($datosValidados['jugador_id'], $datosValidados['equipo_destino_id'], $datosValidados['liga_destino_id'] ?? null, $datosValidados['temporada_id'] ?? null);
        }

        return redirect()->route('encargados.panel')->with('mensaje', 'Solicitud de traspaso registrada.');
    }

    public function responder(Request $solicitud, int $traspaso): RedirectResponse
    {
        $usuarioActual = Auth::user();
        $equiposAdministrados = $this->equiposAdministrados($usuarioActual->id);
        $traspasoActual = DB::table('traspasos')->where('id', $traspaso)->first();

        abort_if(! $traspasoActual, 404);
        abort_if(! in_array((int) $traspasoActual->equipo_origen_id, $equiposAdministrados, true), 403);

        $datosValidados = $solicitud->validate([
            'estado' => ['required', 'in:aprobado,rechazado'],
            'respuesta' => ['nullable', 'string'],
        ]);

        if ($datosValidados['estado'] === 'aprobado') {
            if ($traspasoActual->liga_origen_id && $traspasoActual->liga_origen_id === $traspasoActual->liga_destino_id) {
                DB::table('jugador_equipo_liga')
                    ->where('persona_id', $traspasoActual->jugador_id)
                    ->where('equipo_id', $traspasoActual->equipo_origen_id)
                    ->where('liga_id', $traspasoActual->liga_origen_id)
                    ->where('estado', 'activo')
                    ->update([
                        'estado' => 'inactivo',
                        'fecha_baja' => now()->toDateString(),
                        'updated_at' => now(),
                    ]);
            }

            $this->registrarPlantilla($traspasoActual->jugador_id, $traspasoActual->equipo_destino_id, $traspasoActual->liga_destino_id, $traspasoActual->temporada_id);
        }

        DB::table('traspasos')->where('id', $traspaso)->update([
            'estado' => $datosValidados['estado'],
            'respuesta' => $datosValidados['respuesta'] ?? null,
            'usuario_receptor_id' => $usuarioActual->id,
            'respondido_en' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('encargados.panel')->with('mensaje', 'Traspaso '.$datosValidados['estado'].'.');
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

    private function primerUsuarioDeEquipo(int $equipoId, int $usuarioOmitidoId): ?int
    {
        return DB::table('usuario_equipo')
            ->where('equipo_id', $equipoId)
            ->where('usuario_id', '!=', $usuarioOmitidoId)
            ->where('estado', true)
            ->orderBy('usuario_id')
            ->value('usuario_id');
    }

    private function registrarPlantilla(int $jugadorId, int $equipoId, ?int $ligaId, ?int $temporadaId): void
    {
        DB::table('jugador_equipo_liga')->updateOrInsert(
            [
                'persona_id' => $jugadorId,
                'equipo_id' => $equipoId,
                'liga_id' => $ligaId,
                'temporada_id' => $temporadaId,
            ],
            [
                'fecha_alta' => now()->toDateString(),
                'fecha_baja' => null,
                'estado' => 'activo',
                'origen' => 'traspaso',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
