<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_muestra_sin_datos_cuando_no_hay_temporadas(): void
    {
        $respuesta = $this->get('/');

        $respuesta->assertOk();
        $respuesta->assertSee('Sin datos');
    }

    public function test_index_muestra_temporadas_actuales(): void
    {
        $ligaId = DB::table('ligas')->insertGetId([
            'nombre' => 'Liga Centro',
            'eslogan' => 'Centro',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('temporadas')->insert([
            'liga_id' => $ligaId,
            'nombre' => 'Apertura 2026',
            'desde' => now()->subDay()->toDateString(),
            'hasta' => now()->addMonth()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $respuesta = $this->get('/');

        $respuesta->assertOk();
        $respuesta->assertSee('Liga Centro');
        $respuesta->assertSee('Apertura 2026');
    }

    public function test_footer_publico_muestra_texto_y_suma_visita(): void
    {
        DB::table('empresa')->insert([
            'nombre' => 'Liga Footer',
            'texto_pie_pagina' => 'Texto visible en el pie publico.',
            'visitas' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $respuesta = $this->get('/');

        $respuesta->assertOk();
        $respuesta->assertSee('Texto visible en el pie publico.');
        $respuesta->assertSee('5 visitas');
        $this->assertSame(5, DB::table('empresa')->value('visitas'));
    }

    public function test_tabla_temporada_muestra_equipos_en_cero_sin_partidos_jugados(): void
    {
        $ligaId = DB::table('ligas')->insertGetId([
            'nombre' => 'Liga Centro',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $temporadaId = DB::table('temporadas')->insertGetId([
            'liga_id' => $ligaId,
            'nombre' => 'Apertura 2026',
            'desde' => now()->subDay()->toDateString(),
            'hasta' => now()->addMonth()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipoAId = DB::table('equipos')->insertGetId(['nombre' => 'Alfa FC', 'created_at' => now(), 'updated_at' => now()]);
        $equipoBId = DB::table('equipos')->insertGetId(['nombre' => 'Beta FC', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('partidos')->insert([
            'temporada_id' => $temporadaId,
            'liga_id' => $ligaId,
            'equipo_local' => $equipoAId,
            'equipo_visitante' => $equipoBId,
            'estado' => 'por_jugar',
            'fecha' => now()->addWeek()->toDateString(),
            'hora' => '10:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $respuesta = $this->get(route('publico.temporadas.tabla', $temporadaId));

        $respuesta->assertOk();
        $respuesta->assertSeeInOrder(['Alfa FC', 'Beta FC']);
        $respuesta->assertSee('Proximos partidos');
        $respuesta->assertSee('0');
    }
}
