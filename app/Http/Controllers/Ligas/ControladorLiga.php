<?php

namespace App\Http\Controllers\Ligas;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Support\GestorImagenes;

class ControladorLiga extends Controller
{
    public function index(): View
    {
        $registros = DB::table('ligas')->orderBy('nombre')->paginate(10);

        return view('panel.crud.index', [
            'tituloModulo' => 'Ligas',
            'descripcionModulo' => 'Gestiona las ligas visibles en el portal publico.',
            'registros' => $registros,
            'columnas' => [
                ['campo' => 'nombre', 'etiqueta' => 'Nombre'],
                ['campo' => 'imagen', 'etiqueta' => 'Imagen', 'tipo' => 'imagen'],
                ['campo' => 'eslogan', 'etiqueta' => 'Eslogan'],
                ['campo' => 'descripcion', 'etiqueta' => 'Descripcion'],
            ],
            'rutaCrear' => route('gestion.ligas.create'),
            'rutaEditarNombre' => 'gestion.ligas.edit',
            'rutaEliminarNombre' => 'gestion.ligas.destroy',
        ]);
    }

    public function create(): View
    {
        return $this->formulario('Crear liga', route('gestion.ligas.store'));
    }

    public function store(Request $solicitud): RedirectResponse
    {
        $datosValidados = $this->datosValidados($solicitud);
        $datosValidados['imagen'] = GestorImagenes::guardar($solicitud, 'ligas');

        DB::table('ligas')->insert($datosValidados + ['created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('gestion.ligas.index')->with('mensaje', 'Liga creada correctamente.');
    }

    public function edit(int $liga): View
    {
        $registro = DB::table('ligas')->where('id', $liga)->first();
        abort_if(! $registro, 404);

        return $this->formulario('Editar liga', route('gestion.ligas.update', $liga), $registro, 'PUT');
    }

    public function update(Request $solicitud, int $liga): RedirectResponse
    {
        $ligaActual = DB::table('ligas')->where('id', $liga)->first();
        abort_if(! $ligaActual, 404);

        $datosValidados = $this->datosValidados($solicitud);
        $datosValidados['imagen'] = GestorImagenes::guardar($solicitud, 'ligas', $ligaActual->imagen);

        DB::table('ligas')->where('id', $liga)->update($datosValidados + ['updated_at' => now()]);

        return redirect()->route('gestion.ligas.index')->with('mensaje', 'Liga actualizada correctamente.');
    }

    public function destroy(int $liga): RedirectResponse
    {
        $ligaActual = DB::table('ligas')->where('id', $liga)->first();
        GestorImagenes::eliminar($ligaActual?->imagen);

        DB::table('ligas')->where('id', $liga)->delete();

        return redirect()->route('gestion.ligas.index')->with('mensaje', 'Liga eliminada correctamente.');
    }

    private function datosValidados(Request $solicitud): array
    {
        return $solicitud->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'eslogan' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'imagen' => GestorImagenes::reglas(),
        ]);
    }

    private function formulario(string $tituloFormulario, string $accionFormulario, object $registro = null, string $metodoFormulario = 'POST'): View
    {
        return view('panel.crud.formulario', [
            'tituloFormulario' => $tituloFormulario,
            'accionFormulario' => $accionFormulario,
            'metodoFormulario' => $metodoFormulario,
            'rutaRegreso' => route('gestion.ligas.index'),
            'registro' => $registro,
            'campos' => [
                ['nombre' => 'nombre', 'etiqueta' => 'Nombre', 'tipo' => 'text', 'requerido' => true],
                ['nombre' => 'eslogan', 'etiqueta' => 'Eslogan', 'tipo' => 'text'],
                ['nombre' => 'descripcion', 'etiqueta' => 'Descripcion', 'tipo' => 'textarea'],
                ['nombre' => 'imagen', 'etiqueta' => 'Imagen', 'tipo' => 'file'],
            ],
        ]);
    }
}
