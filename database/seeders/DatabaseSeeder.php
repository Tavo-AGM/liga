<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('empresa')->updateOrInsert(
            ['id' => 1],
            [
                'nombre' => 'Liga Local',
                'eslogan' => 'Gestion deportiva local',
                'descripcion' => 'Plataforma para administrar ligas, torneos, equipos, jugadores y partidos.',
                'texto_pie_pagina' => 'Sistema de gestion deportiva para ligas locales.',
                'color_primario' => '#047857',
                'color_secundario' => '#0f172a',
                'color_acento' => '#10b981',
                'color_fondo' => '#f4f4f5',
                'color_texto' => '#ffffff',
                'color_menu' => '#ffffff',
                'color_pie_pagina' => '#ffffff',
                'visitas' => 0,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        foreach (['Administrador', 'Arbitro', 'Encargado de equipo'] as $tipoUsuario) {
            DB::table('usuario_tipo')->updateOrInsert(
                ['nombre' => $tipoUsuario],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $tipoAdministrador = DB::table('usuario_tipo')->where('nombre', 'Administrador')->first();

        User::updateOrCreate(
            ['email' => 'admin@liga.local'],
            [
                'nombre' => 'Admin',
                'contrasena' => Hash::make('admin'),
                'fecha_creacion' => now()->toDateString(),
                'tipo_usuario' => $tipoAdministrador?->id,
                'estado' => true,
                'es_usuario_sistema' => true,
            ]
        );
    }
}
