<?php

namespace App\Http\Controllers\Personas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControladorJugador extends ControladorPersonaBase
{
    protected function tipoPersona(): string
    {
        return 'jugador';
    }

    protected function tituloModulo(): string
    {
        return 'Jugadores';
    }

    protected function rutaBase(): string
    {
        return 'gestion.jugadores';
    }

    protected function tituloSingular(): string
    {
        return 'Jugador';
    }

    protected function camposFormulario(): array
    {
        $campos = parent::camposFormulario();
        $ligas = DB::table('ligas')->orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $temporadas = DB::table('temporadas')
            ->leftJoin('ligas', 'temporadas.liga_id', '=', 'ligas.id')
            ->select('temporadas.id', DB::raw("concat(coalesce(ligas.nombre, 'Liga sin nombre'), ' / ', temporadas.nombre) as nombre_completo"))
            ->orderBy('ligas.nombre')
            ->orderBy('temporadas.nombre')
            ->pluck('nombre_completo', 'temporadas.id')
            ->toArray();

        $camposConRelacion = [];

        foreach ($campos as $campo) {
            $camposConRelacion[] = $campo;

            if ($campo['nombre'] === 'equipo_id') {
                $camposConRelacion[] = ['nombre' => 'liga_id', 'etiqueta' => 'Liga para plantilla inicial', 'tipo' => 'select', 'opciones' => $ligas];
                $camposConRelacion[] = ['nombre' => 'temporada_id', 'etiqueta' => 'Temporada para plantilla inicial', 'tipo' => 'select', 'opciones' => $temporadas];
            }
        }

        return $camposConRelacion;
    }

    protected function datosValidados(Request $solicitud): array
    {
        return $solicitud->validate([
            'equipo_id' => ['nullable', 'exists:equipos,id'],
            'liga_id' => ['nullable', 'exists:ligas,id'],
            'temporada_id' => ['nullable', 'exists:temporadas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'imagen' => \App\Support\GestorImagenes::reglas(),
            'estado' => ['required', 'boolean'],
        ]);
    }

    protected function datosPersona(array $datosValidados): array
    {
        unset($datosValidados['liga_id'], $datosValidados['temporada_id']);

        return $datosValidados;
    }

    protected function despuesDeGuardarPersona(int $personaId, array $datosValidados): void
    {
        if (empty($datosValidados['equipo_id'])) {
            return;
        }

        DB::table('jugador_equipo_liga')->updateOrInsert(
            [
                'persona_id' => $personaId,
                'equipo_id' => $datosValidados['equipo_id'],
                'liga_id' => $datosValidados['liga_id'] ?? null,
                'temporada_id' => $datosValidados['temporada_id'] ?? null,
            ],
            [
                'fecha_alta' => now()->toDateString(),
                'estado' => 'activo',
                'origen' => 'registro',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
