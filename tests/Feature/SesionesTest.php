<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SesionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_iniciar_sesion(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $respuesta = $this->withSession(['_token' => 'token-prueba'])->post('/login', [
            '_token' => 'token-prueba',
            'email' => 'admin@liga.local',
            'password' => 'admin',
        ]);

        $respuesta->assertRedirect(route('panel.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_panel_requiere_sesion(): void
    {
        $respuesta = $this->get('/panel');

        $respuesta->assertRedirect(route('sesiones.login'));
    }

    public function test_cerrar_sesion_redirige_al_inicio(): void
    {
        $tipoAdministradorId = $this->crearTipoUsuario('Administrador');

        $usuarioAdministrador = User::create([
            'nombre' => 'Admin',
            'email' => 'admin@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoAdministradorId,
            'estado' => true,
        ]);

        $respuesta = $this->actingAs($usuarioAdministrador)
            ->withSession(['_token' => 'token-prueba'])
            ->post('/logout', ['_token' => 'token-prueba']);

        $respuesta->assertRedirect(route('publico.index'));
        $this->assertGuest();
    }

    public function test_arbitro_inicia_en_su_panel(): void
    {
        $tipoArbitroId = $this->crearTipoUsuario('Arbitro');

        User::create([
            'nombre' => 'Arbitro',
            'email' => 'arbitro@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoArbitroId,
            'estado' => true,
        ]);

        $respuesta = $this->withSession(['_token' => 'token-prueba'])->post('/login', [
            '_token' => 'token-prueba',
            'email' => 'arbitro@liga.local',
            'password' => 'admin',
        ]);

        $respuesta->assertRedirect(route('arbitros.panel'));
    }

    public function test_encargado_inicia_en_su_panel(): void
    {
        $tipoEncargadoId = $this->crearTipoUsuario('Encargado de equipo');

        User::create([
            'nombre' => 'Encargado',
            'email' => 'encargado@liga.local',
            'contrasena' => Hash::make('admin'),
            'tipo_usuario' => $tipoEncargadoId,
            'estado' => true,
        ]);

        $respuesta = $this->withSession(['_token' => 'token-prueba'])->post('/login', [
            '_token' => 'token-prueba',
            'email' => 'encargado@liga.local',
            'password' => 'admin',
        ]);

        $respuesta->assertRedirect(route('encargados.panel'));
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
