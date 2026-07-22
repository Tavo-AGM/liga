<?php

namespace App\Http\Controllers\Personas;

class ControladorArbitro extends ControladorPersonaBase
{
    protected function tipoPersona(): string
    {
        return 'arbitro';
    }

    protected function tituloModulo(): string
    {
        return 'Arbitros';
    }

    protected function rutaBase(): string
    {
        return 'gestion.arbitros';
    }

    protected function tituloSingular(): string
    {
        return 'Arbitro';
    }

    protected function camposFormulario(): array
    {
        $campos = parent::camposFormulario();

        return array_values(array_filter($campos, fn ($campo) => $campo['nombre'] !== 'equipo_id'));
    }
}
