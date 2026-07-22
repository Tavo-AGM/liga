<?php

namespace App\Http\Controllers\Temporadas;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorTemporada extends Controller
{
    public function index(): View
    {
        $registros = DB::table('temporadas')
            ->leftJoin('ligas', 'temporadas.liga_id', '=', 'ligas.id')
            ->select('temporadas.id', 'temporadas.nombre', 'temporadas.desde', 'temporadas.hasta', 'ligas.nombre as liga_nombre')
            ->orderByDesc('temporadas.desde')
            ->paginate(10);

        return view('panel.crud.index', [
            'tituloModulo' => 'Temporadas',
            'descripcionModulo' => 'Gestiona periodos de competencia por liga.',
            'registros' => $registros,
            'columnas' => [
                ['campo' => 'nombre', 'etiqueta' => 'Nombre'],
                ['campo' => 'liga_nombre', 'etiqueta' => 'Liga'],
                ['campo' => 'desde', 'etiqueta' => 'Desde'],
                ['campo' => 'hasta', 'etiqueta' => 'Hasta'],
            ],
            'rutaCrear' => route('gestion.temporadas.create'),
            'rutaEditarNombre' => 'gestion.temporadas.edit',
            'rutaEliminarNombre' => 'gestion.temporadas.destroy',
        ]);
    }

    public function create(): View
    {
        return $this->formulario('Crear temporada', route('gestion.temporadas.store'));
    }

    public function store(Request $solicitud): RedirectResponse
    {
        DB::table('temporadas')->insert($this->datosValidados($solicitud) + ['created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('gestion.temporadas.index')->with('mensaje', 'Temporada creada correctamente.');
    }

    public function edit(int $temporada): View
    {
        $registro = DB::table('temporadas')->where('id', $temporada)->first();
        abort_if(! $registro, 404);

        return $this->formulario('Editar temporada', route('gestion.temporadas.update', $temporada), $registro, 'PUT');
    }

    public function update(Request $solicitud, int $temporada): RedirectResponse
    {
        DB::table('temporadas')->where('id', $temporada)->update($this->datosValidados($solicitud) + ['updated_at' => now()]);

        return redirect()->route('gestion.temporadas.index')->with('mensaje', 'Temporada actualizada correctamente.');
    }

    public function destroy(int $temporada): RedirectResponse
    {
        DB::table('temporadas')->where('id', $temporada)->delete();

        return redirect()->route('gestion.temporadas.index')->with('mensaje', 'Temporada eliminada correctamente.');
    }

    private function datosValidados(Request $solicitud): array
    {
        return $solicitud->validate([
            'liga_id' => ['nullable', 'exists:ligas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);
    }

    private function formulario(string $tituloFormulario, string $accionFormulario, object $registro = null, string $metodoFormulario = 'POST'): View
    {
        $ligas = DB::table('ligas')->orderBy('nombre')->pluck('nombre', 'id')->toArray();

        return view('panel.crud.formulario', [
            'tituloFormulario' => $tituloFormulario,
            'accionFormulario' => $accionFormulario,
            'metodoFormulario' => $metodoFormulario,
            'rutaRegreso' => route('gestion.temporadas.index'),
            'registro' => $registro,
            'campos' => [
                ['nombre' => 'liga_id', 'etiqueta' => 'Liga', 'tipo' => 'select', 'opciones' => $ligas],
                ['nombre' => 'nombre', 'etiqueta' => 'Nombre', 'tipo' => 'text', 'requerido' => true],
                ['nombre' => 'desde', 'etiqueta' => 'Desde', 'tipo' => 'date'],
                ['nombre' => 'hasta', 'etiqueta' => 'Hasta', 'tipo' => 'date'],
            ],
        ]);
    }
}
