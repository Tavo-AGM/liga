<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class ConfiguracionEmpresa
{
    public static function obtener(): object
    {
        $valoresDefecto = [
            'id' => null,
            'nombre' => 'Liga Local',
            'eslogan' => 'Gestion deportiva local',
            'descripcion' => 'Plataforma para administrar ligas, torneos, equipos, jugadores y partidos.',
            'texto_pie_pagina' => 'Sistema de gestion deportiva para ligas locales.',
            'imagen' => null,
            'color_primario' => '#047857',
            'color_secundario' => '#0f172a',
            'color_acento' => '#10b981',
            'color_fondo' => '#f4f4f5',
            'color_texto' => '#ffffff',
            'color_menu' => '#ffffff',
            'color_pie_pagina' => '#ffffff',
            'visitas' => 0,
        ];

        if (! DB::getSchemaBuilder()->hasTable('empresa')) {
            return (object) $valoresDefecto;
        }

        $empresa = DB::table('empresa')->orderBy('id')->first();

        if (! $empresa) {
            return (object) $valoresDefecto;
        }

        return (object) array_merge($valoresDefecto, (array) $empresa);
    }
}
