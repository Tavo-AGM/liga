<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerificarTipoUsuario
{
    public function handle(Request $solicitud, Closure $siguiente, string ...$tiposPermitidos): Response
    {
        $usuario = Auth::user();

        if (! $usuario) {
            return redirect()->route('sesiones.login');
        }

        $tipoUsuario = DB::table('usuario_tipo')->where('id', $usuario->tipo_usuario)->value('nombre');

        if (in_array($tipoUsuario, $tiposPermitidos, true)) {
            return $siguiente($solicitud);
        }

        return redirect()->route($this->rutaPorTipo($tipoUsuario));
    }

    private function rutaPorTipo(?string $tipoUsuario): string
    {
        return match ($tipoUsuario) {
            'Arbitro' => 'arbitros.panel',
            'Encargado de equipo' => 'encargados.panel',
            'Administrador' => 'panel.dashboard',
            default => 'publico.index',
        };
    }
}
