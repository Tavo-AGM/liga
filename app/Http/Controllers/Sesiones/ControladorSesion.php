<?php

namespace App\Http\Controllers\Sesiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorSesion extends Controller
{
    public function mostrarFormulario(): View
    {
        return view('sesiones.login');
    }

    public function iniciarSesion(Request $solicitud): RedirectResponse
    {
        $credenciales = $solicitud->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $recordarSesion = $solicitud->boolean('recordarSesion');

        if (Auth::attempt($credenciales + ['estado' => true], $recordarSesion)) {
            $solicitud->session()->regenerate();

            return redirect()->intended(route($this->rutaDespuesDeIniciarSesion()));
        }

        return back()
            ->withErrors(['email' => 'Las credenciales no son correctas o el usuario esta inactivo.'])
            ->onlyInput('email');
    }

    public function cerrarSesion(Request $solicitud): RedirectResponse
    {
        Auth::logout();

        $solicitud->session()->invalidate();
        $solicitud->session()->regenerateToken();

        return redirect()->route('publico.index');
    }

    private function rutaDespuesDeIniciarSesion(): string
    {
        $tipoUsuario = DB::table('usuario_tipo')
            ->where('id', Auth::user()?->tipo_usuario)
            ->value('nombre');

        return match ($tipoUsuario) {
            'Arbitro' => 'arbitros.panel',
            'Encargado de equipo' => 'encargados.panel',
            default => 'panel.dashboard',
        };
    }
}
