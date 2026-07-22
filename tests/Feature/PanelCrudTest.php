<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanelCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_modulos_crud_cargan_para_administrador(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $rutas = [
            route('gestion.usuarios.index'),
            route('gestion.ligas.index'),
            route('gestion.equipos.index'),
            route('gestion.jugadores.index'),
            route('gestion.campos.index'),
            route('gestion.arbitros.index'),
            route('gestion.temporadas.index'),
            route('gestion.partidos.index'),
            route('gestion.empresa.edit'),
            route('gestion.plantillas.index'),
            route('gestion.plantillas.create'),
            route('gestion.traspasos.index'),
            route('gestion.ligas.create'),
            route('gestion.calendarios.temporada.create'),
            route('gestion.torneos.generar.create'),
        ];

        foreach ($rutas as $ruta) {
            $this->actingAs($usuarioAdministrador)
                ->get($ruta)
                ->assertOk();
        }
    }

    public function test_puede_subir_imagen_png_en_ligas(): void
    {
        Storage::fake('public');

        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $respuesta = $this->actingAs($usuarioAdministrador)
            ->post(route('gestion.ligas.store'), [
                'nombre' => 'Liga con imagen',
                'eslogan' => 'Futbol local',
                'descripcion' => 'Liga de prueba',
                'imagen' => UploadedFile::fake()->image('liga.png', 800, 800),
            ]);

        $respuesta->assertRedirect(route('gestion.ligas.index'));

        $liga = \Illuminate\Support\Facades\DB::table('ligas')->where('nombre', 'Liga con imagen')->first();

        $this->assertNotNull($liga);
        $this->assertStringStartsWith('/storage/ligas/', $liga->imagen);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $liga->imagen));
    }

    public function test_administrador_actualiza_empresa_icono_y_paleta(): void
    {
        Storage::fake('public');

        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $respuesta = $this->actingAs($usuarioAdministrador)
            ->put(route('gestion.empresa.update'), [
                'nombre' => 'Liga Metropolitana',
                'eslogan' => 'Futbol para todos',
                'descripcion' => 'Nueva identidad visual.',
                'texto_pie_pagina' => 'Pie publico actualizado.',
                'imagen' => UploadedFile::fake()->image('icono.jpg', 512, 512),
                'color_primario' => '#1d4ed8',
                'color_secundario' => '#111827',
                'color_acento' => '#f59e0b',
                'color_fondo' => '#f8fafc',
                'color_texto' => '#ffffff',
                'color_menu' => '#e0f2fe',
                'color_pie_pagina' => '#fef3c7',
            ]);

        $respuesta->assertRedirect(route('gestion.empresa.edit'));

        $empresa = DB::table('empresa')->first();

        $this->assertSame('Liga Metropolitana', $empresa->nombre);
        $this->assertSame('Pie publico actualizado.', $empresa->texto_pie_pagina);
        $this->assertSame('#1d4ed8', $empresa->color_primario);
        $this->assertSame('#e0f2fe', $empresa->color_menu);
        $this->assertSame('#fef3c7', $empresa->color_pie_pagina);
        $this->assertStringStartsWith('/storage/empresa/', $empresa->imagen);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $empresa->imagen));
    }

    public function test_administrador_reinicia_contador_de_visitas(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        DB::table('empresa')->insert([
            'nombre' => 'Liga Local',
            'visitas' => 27,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $respuesta = $this->actingAs($usuarioAdministrador)
            ->post(route('gestion.empresa.reiniciar-visitas'));

        $respuesta->assertRedirect(route('gestion.empresa.edit'));
        $this->assertSame(0, DB::table('empresa')->value('visitas'));
    }

    public function test_usuario_admin_por_defecto_no_se_puede_eliminar(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
            'es_usuario_sistema' => true,
        ]);

        $this->actingAs($usuarioAdministrador)
            ->get(route('gestion.usuarios.index'))
            ->assertOk()
            ->assertSee('Protegido');

        $respuesta = $this->actingAs($usuarioAdministrador)
            ->delete(route('gestion.usuarios.destroy', $usuarioAdministrador->id));

        $respuesta->assertRedirect(route('gestion.usuarios.index'));
        $this->assertDatabaseHas('usuarios', [
            'id' => $usuarioAdministrador->id,
            'email' => 'admin@liga.local',
        ]);
    }

    public function test_puede_generar_calendario_de_temporada(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $ligaId = DB::table('ligas')->insertGetId([
            'nombre' => 'Liga Prueba',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $temporadaId = DB::table('temporadas')->insertGetId([
            'liga_id' => $ligaId,
            'nombre' => 'Apertura',
            'desde' => now()->next('Saturday')->toDateString(),
            'hasta' => now()->addMonths(2)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $campoUnoId = DB::table('campos')->insertGetId(['nombre' => 'Campo 1', 'created_at' => now(), 'updated_at' => now()]);
        $campoDosId = DB::table('campos')->insertGetId(['nombre' => 'Campo 2', 'created_at' => now(), 'updated_at' => now()]);

        $equipoUnoId = DB::table('equipos')->insertGetId(['campo_id' => $campoUnoId, 'nombre' => 'Equipo 1', 'created_at' => now(), 'updated_at' => now()]);
        $equipoDosId = DB::table('equipos')->insertGetId(['campo_id' => $campoDosId, 'nombre' => 'Equipo 2', 'created_at' => now(), 'updated_at' => now()]);

        $respuesta = $this->actingAs($usuarioAdministrador)
            ->post(route('gestion.calendarios.temporada.store'), [
                'temporada_id' => $temporadaId,
                'equipos' => [$equipoUnoId, $equipoDosId],
                'ida_vuelta' => 1,
                'dias_juego' => ['6'],
                'horarios' => '09:00, 11:30',
                'tolerancia_horas' => 2,
            ]);

        $respuesta->assertRedirect(route('gestion.calendarios.temporada.create'));
        $this->assertSame(2, DB::table('partidos')->where('temporada_id', $temporadaId)->count());
        $this->assertSame(2, DB::table('partidos')->where('temporada_id', $temporadaId)->where('estado', 'por_jugar')->count());
    }

    public function test_usuario_encargado_puede_vincularse_a_varios_equipos(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');
        $tipoEncargadoId = $this->crearTipoUsuario('Encargado de equipo');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $equipoUnoId = DB::table('equipos')->insertGetId(['nombre' => 'Equipo Uno', 'created_at' => now(), 'updated_at' => now()]);
        $equipoDosId = DB::table('equipos')->insertGetId(['nombre' => 'Equipo Dos', 'created_at' => now(), 'updated_at' => now()]);

        $respuesta = $this->actingAs($usuarioAdministrador)
            ->post(route('gestion.usuarios.store'), [
                'nombre' => 'Encargado',
                'email' => 'encargado@liga.local',
                'contrasena' => 'admin',
                'tipo_usuario' => $tipoEncargadoId,
                'equipo_id' => $equipoUnoId,
                'equipos' => [$equipoUnoId, $equipoDosId],
                'estado' => true,
            ]);

        $respuesta->assertRedirect(route('gestion.usuarios.index'));

        $usuarioId = DB::table('usuarios')->where('email', 'encargado@liga.local')->value('id');

        $this->assertSame(2, DB::table('usuario_equipo')->where('usuario_id', $usuarioId)->count());
    }

    public function test_jugador_puede_tener_plantillas_en_ligas_distintas(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $ligaUnoId = DB::table('ligas')->insertGetId(['nombre' => 'Fuerza 2', 'created_at' => now(), 'updated_at' => now()]);
        $ligaDosId = DB::table('ligas')->insertGetId(['nombre' => 'Fuerza 3', 'created_at' => now(), 'updated_at' => now()]);
        $equipoUnoId = DB::table('equipos')->insertGetId(['nombre' => 'Equipo A', 'created_at' => now(), 'updated_at' => now()]);
        $equipoDosId = DB::table('equipos')->insertGetId(['nombre' => 'Equipo B', 'created_at' => now(), 'updated_at' => now()]);
        $jugadorId = DB::table('personas')->insertGetId([
            'tipo_persona' => 'jugador',
            'nombre' => 'Jugador',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([[$equipoUnoId, $ligaUnoId], [$equipoDosId, $ligaDosId]] as [$equipoId, $ligaId]) {
            $respuesta = $this->actingAs($usuarioAdministrador)
                ->post(route('gestion.plantillas.store'), [
                    'persona_id' => $jugadorId,
                    'equipo_id' => $equipoId,
                    'liga_id' => $ligaId,
                    'estado' => 'activo',
                    'origen' => 'registro',
                ]);

            $respuesta->assertRedirect(route('gestion.plantillas.index'));
        }

        $this->assertSame(2, DB::table('jugador_equipo_liga')->where('persona_id', $jugadorId)->where('estado', 'activo')->count());
    }

    public function test_encargado_recibe_y_aprueba_traspaso(): void
    {
        $tipoEncargadoId = $this->crearTipoUsuario('Encargado de equipo');

        $ligaId = DB::table('ligas')->insertGetId(['nombre' => 'Liga Traspasos', 'created_at' => now(), 'updated_at' => now()]);
        $equipoOrigenId = DB::table('equipos')->insertGetId(['nombre' => 'Origen FC', 'created_at' => now(), 'updated_at' => now()]);
        $equipoDestinoId = DB::table('equipos')->insertGetId(['nombre' => 'Destino FC', 'created_at' => now(), 'updated_at' => now()]);
        $jugadorId = DB::table('personas')->insertGetId([
            'tipo_persona' => 'jugador',
            'nombre' => 'Fichaje',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('jugador_equipo_liga')->insert([
            'persona_id' => $jugadorId,
            'equipo_id' => $equipoOrigenId,
            'liga_id' => $ligaId,
            'estado' => 'activo',
            'origen' => 'registro',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $duenoOrigen = User::create([
            'nombre' => 'Dueno origen',
            'email' => 'origen@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoEncargadoId,
            'equipo_id' => $equipoOrigenId,
            'estado' => true,
        ]);
        $duenoDestino = User::create([
            'nombre' => 'Dueno destino',
            'email' => 'destino@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoEncargadoId,
            'equipo_id' => $equipoDestinoId,
            'estado' => true,
        ]);

        foreach ([[$duenoOrigen->id, $equipoOrigenId], [$duenoDestino->id, $equipoDestinoId]] as [$usuarioId, $equipoId]) {
            DB::table('usuario_equipo')->insert([
                'usuario_id' => $usuarioId,
                'equipo_id' => $equipoId,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($duenoDestino)
            ->post(route('encargados.traspasos.store'), [
                'jugador_id' => $jugadorId,
                'equipo_destino_id' => $equipoDestinoId,
                'liga_destino_id' => $ligaId,
                'mensaje' => 'Solicitud de fichaje',
            ])
            ->assertRedirect(route('encargados.panel'));

        $traspasoId = DB::table('traspasos')->where('jugador_id', $jugadorId)->value('id');

        $this->actingAs($duenoOrigen)
            ->get(route('encargados.panel'))
            ->assertOk()
            ->assertSee('Solicitudes pendientes')
            ->assertSee('Fichaje');

        $this->actingAs($duenoOrigen)
            ->patch(route('encargados.traspasos.responder', $traspasoId), ['estado' => 'aprobado'])
            ->assertRedirect(route('encargados.panel'));

        $this->assertSame('aprobado', DB::table('traspasos')->where('id', $traspasoId)->value('estado'));
        $this->assertSame(1, DB::table('jugador_equipo_liga')->where('persona_id', $jugadorId)->where('equipo_id', $equipoDestinoId)->where('estado', 'activo')->count());
        $this->assertSame(1, DB::table('jugador_equipo_liga')->where('persona_id', $jugadorId)->where('equipo_id', $equipoOrigenId)->where('estado', 'inactivo')->count());
    }

    public function test_dashboard_muestra_partidos_por_jugar(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $equipoLocalId = DB::table('equipos')->insertGetId(['nombre' => 'Local FC', 'created_at' => now(), 'updated_at' => now()]);
        $equipoVisitanteId = DB::table('equipos')->insertGetId(['nombre' => 'Visitante FC', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('partidos')->insert([
            'equipo_local' => $equipoLocalId,
            'equipo_visitante' => $equipoVisitanteId,
            'estado' => 'por_jugar',
            'fecha' => now()->addDay()->toDateString(),
            'hora' => '12:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $respuesta = $this->actingAs($usuarioAdministrador)->get(route('panel.dashboard'));

        $respuesta->assertOk();
        $respuesta->assertSee('Partidos por jugar');
        $respuesta->assertSee('Local FC');
        $respuesta->assertSee('Visitante FC');
    }

    public function test_puede_editar_fecha_hora_campo_y_estado_de_partido(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $campoId = DB::table('campos')->insertGetId(['nombre' => 'Campo nuevo', 'created_at' => now(), 'updated_at' => now()]);
        $partidoId = DB::table('partidos')->insertGetId([
            'estado' => 'por_jugar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $respuesta = $this->actingAs($usuarioAdministrador)
            ->put(route('gestion.partidos.update', $partidoId), [
                'fecha' => '2026-08-01',
                'hora' => '13:30',
                'campo_id' => $campoId,
                'estado' => 'aplazado',
            ]);

        $respuesta->assertRedirect(route('gestion.partidos.index'));

        $partido = DB::table('partidos')->where('id', $partidoId)->first();

        $this->assertSame('2026-08-01', $partido->fecha);
        $this->assertSame('13:30:00', $partido->hora);
        $this->assertSame($campoId, $partido->campo_id);
        $this->assertSame('aplazado', $partido->estado);
    }

    private function crearTipoUsuario(string $nombre): int
    {
        return DB::table('usuario_tipo')->insertGetId([
            'nombre' => $nombre,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
