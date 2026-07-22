<?php

namespace App\Http\Controllers\Campos;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorCampo extends Controller
{
    public function index(): View
    {
        $registros = DB::table('campos')->orderBy('nombre')->paginate(10);

        return view('panel.crud.index', [
            'tituloModulo' => 'Campos',
            'descripcionModulo' => 'Administra sedes y ubicaciones de juego.',
            'registros' => $registros,
            'columnas' => [
                ['campo' => 'nombre', 'etiqueta' => 'Nombre'],
                ['campo' => 'ubicacion', 'etiqueta' => 'Ubicacion'],
                ['campo' => 'descripcion', 'etiqueta' => 'Descripcion'],
            ],
            'rutaCrear' => route('gestion.campos.create'),
            'rutaEditarNombre' => 'gestion.campos.edit',
            'rutaEliminarNombre' => 'gestion.campos.destroy',
        ]);
    }

    public function create(): View
    {
        return $this->formulario('Crear campo', route('gestion.campos.store'));
    }

    public function store(Request $solicitud): RedirectResponse
    {
        DB::table('campos')->insert($this->datosValidados($solicitud) + ['created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('gestion.campos.index')->with('mensaje', 'Campo creado correctamente.');
    }

    public function edit(int $campo): View
    {
        $registro = DB::table('campos')->where('id', $campo)->first();
        abort_if(! $registro, 404);

        return $this->formulario('Editar campo', route('gestion.campos.update', $campo), $registro, 'PUT');
    }

    public function update(Request $solicitud, int $campo): RedirectResponse
    {
        DB::table('campos')->where('id', $campo)->update($this->datosValidados($solicitud) + ['updated_at' => now()]);

        return redirect()->route('gestion.campos.index')->with('mensaje', 'Campo actualizado correctamente.');
    }

    public function destroy(int $campo): RedirectResponse
    {
        DB::table('campos')->where('id', $campo)->delete();

        return redirect()->route('gestion.campos.index')->with('mensaje', 'Campo eliminado correctamente.');
    }

    private function datosValidados(Request $solicitud): array
    {
        return $solicitud->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ]);
    }

    private function formulario(string $tituloFormulario, string $accionFormulario, object $registro = null, string $metodoFormulario = 'POST'): View
    {
        return view('panel.crud.formulario', [
            'tituloFormulario' => $tituloFormulario,
            'accionFormulario' => $accionFormulario,
            'metodoFormulario' => $metodoFormulario,
            'rutaRegreso' => route('gestion.campos.index'),
            'registro' => $registro,
            'campos' => [
                ['nombre' => 'nombre', 'etiqueta' => 'Nombre', 'tipo' => 'text', 'requerido' => true],
                ['nombre' => 'ubicacion', 'etiqueta' => 'Ubicacion', 'tipo' => 'text'],
                ['nombre' => 'descripcion', 'etiqueta' => 'Descripcion', 'tipo' => 'textarea'],
            ],
        ]);
    }
}
