<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Support\ConfiguracionEmpresa;
use App\Support\GestorImagenes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorEmpresa extends Controller
{
    public function edit(): View
    {
        $empresaActual = $this->obtenerOCrearEmpresa();

        return view('empresa.formulario', [
            'empresaActual' => $empresaActual,
            'empresaSistema' => ConfiguracionEmpresa::obtener(),
        ]);
    }

    public function update(Request $solicitud): RedirectResponse
    {
        $empresaActual = $this->obtenerOCrearEmpresa();
        $datosValidados = $this->datosValidados($solicitud);
        $datosValidados['imagen'] = GestorImagenes::guardar($solicitud, 'empresa', $empresaActual->imagen);

        DB::table('empresa')->where('id', $empresaActual->id)->update($datosValidados + [
            'updated_at' => now(),
        ]);

        return redirect()->route('gestion.empresa.edit')->with('mensaje', 'Empresa actualizada correctamente.');
    }

    public function reiniciarVisitas(): RedirectResponse
    {
        $empresaActual = $this->obtenerOCrearEmpresa();

        DB::table('empresa')->where('id', $empresaActual->id)->update([
            'visitas' => 0,
            'updated_at' => now(),
        ]);

        return redirect()->route('gestion.empresa.edit')->with('mensaje', 'Contador de visitas reiniciado correctamente.');
    }

    private function obtenerOCrearEmpresa(): object
    {
        $empresaActual = DB::table('empresa')->orderBy('id')->first();

        if ($empresaActual) {
            return $empresaActual;
        }

        $empresaId = DB::table('empresa')->insertGetId([
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('empresa')->where('id', $empresaId)->first();
    }

    private function datosValidados(Request $solicitud): array
    {
        return $solicitud->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'eslogan' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'texto_pie_pagina' => ['nullable', 'string'],
            'imagen' => GestorImagenes::reglas(),
            'color_primario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_secundario' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_acento' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_fondo' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_texto' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_menu' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_pie_pagina' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
    }
}
