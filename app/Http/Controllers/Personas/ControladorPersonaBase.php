<?php

namespace App\Http\Controllers\Personas;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Support\GestorImagenes;

abstract class ControladorPersonaBase extends Controller
{
    abstract protected function tipoPersona(): string;

    abstract protected function tituloModulo(): string;

    abstract protected function rutaBase(): string;

    public function index(): View
    {
        $registros = DB::table('personas')
            ->leftJoin('equipos', 'personas.equipo_id', '=', 'equipos.id')
            ->select('personas.id', 'personas.nombre', 'personas.apellido_paterno', 'personas.apellido_materno', 'personas.fecha_nacimiento', 'personas.imagen', 'personas.estado', 'equipos.nombre as equipo_nombre')
            ->where('personas.tipo_persona', $this->tipoPersona())
            ->orderBy('personas.nombre')
            ->paginate(10);

        $registros->getCollection()->transform(function ($persona) {
            $persona->nombreCompleto = trim($persona->nombre.' '.$persona->apellido_paterno.' '.$persona->apellido_materno);
            $persona->estadoTexto = $persona->estado ? 'Activo' : 'Inactivo';
            return $persona;
        });

        return view('panel.crud.index', [
            'tituloModulo' => $this->tituloModulo(),
            'descripcionModulo' => 'Administra registros de '.$this->tituloModulo().'.',
            'registros' => $registros,
            'columnas' => [
                ['campo' => 'nombreCompleto', 'etiqueta' => 'Nombre'],
                ['campo' => 'imagen', 'etiqueta' => 'Imagen', 'tipo' => 'imagen'],
                ['campo' => 'equipo_nombre', 'etiqueta' => 'Equipo'],
                ['campo' => 'fecha_nacimiento', 'etiqueta' => 'Nacimiento'],
                ['campo' => 'estadoTexto', 'etiqueta' => 'Estado'],
            ],
            'rutaCrear' => route($this->rutaBase().'.create'),
            'rutaEditarNombre' => $this->rutaBase().'.edit',
            'rutaEliminarNombre' => $this->rutaBase().'.destroy',
        ]);
    }

    public function create(): View
    {
        return $this->formulario('Crear '.$this->tituloSingular(), route($this->rutaBase().'.store'));
    }

    public function store(Request $solicitud): RedirectResponse
    {
        $datosValidados = $this->datosValidados($solicitud);
        $datosValidados['imagen'] = GestorImagenes::guardar($solicitud, $this->carpetaImagenes());
        $datosPersona = $this->datosPersona($datosValidados);

        $personaId = DB::table('personas')->insertGetId($datosPersona + [
            'tipo_persona' => $this->tipoPersona(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->despuesDeGuardarPersona($personaId, $datosValidados);

        return redirect()->route($this->rutaBase().'.index')->with('mensaje', $this->tituloSingular().' creado correctamente.');
    }

    public function edit(int $persona): View
    {
        $registro = DB::table('personas')
            ->where('id', $persona)
            ->where('tipo_persona', $this->tipoPersona())
            ->first();

        abort_if(! $registro, 404);

        return $this->formulario('Editar '.$this->tituloSingular(), route($this->rutaBase().'.update', $persona), $registro, 'PUT');
    }

    public function update(Request $solicitud, int $persona): RedirectResponse
    {
        $personaActual = DB::table('personas')
            ->where('id', $persona)
            ->where('tipo_persona', $this->tipoPersona())
            ->first();

        abort_if(! $personaActual, 404);

        $datosValidados = $this->datosValidados($solicitud);
        $datosValidados['imagen'] = GestorImagenes::guardar($solicitud, $this->carpetaImagenes(), $personaActual->imagen);
        $datosPersona = $this->datosPersona($datosValidados);

        DB::table('personas')
            ->where('id', $persona)
            ->where('tipo_persona', $this->tipoPersona())
            ->update($datosPersona + ['updated_at' => now()]);
        $this->despuesDeGuardarPersona($persona, $datosValidados);

        return redirect()->route($this->rutaBase().'.index')->with('mensaje', $this->tituloSingular().' actualizado correctamente.');
    }

    public function destroy(int $persona): RedirectResponse
    {
        $personaActual = DB::table('personas')
            ->where('id', $persona)
            ->where('tipo_persona', $this->tipoPersona())
            ->first();

        GestorImagenes::eliminar($personaActual?->imagen);

        DB::table('personas')
            ->where('id', $persona)
            ->where('tipo_persona', $this->tipoPersona())
            ->delete();

        return redirect()->route($this->rutaBase().'.index')->with('mensaje', $this->tituloSingular().' eliminado correctamente.');
    }

    protected function camposFormulario(): array
    {
        $equipos = DB::table('equipos')->orderBy('nombre')->pluck('nombre', 'id')->toArray();

        return [
            ['nombre' => 'nombre', 'etiqueta' => 'Nombre', 'tipo' => 'text', 'requerido' => true],
            ['nombre' => 'apellido_paterno', 'etiqueta' => 'Apellido paterno', 'tipo' => 'text'],
            ['nombre' => 'apellido_materno', 'etiqueta' => 'Apellido materno', 'tipo' => 'text'],
            ['nombre' => 'fecha_nacimiento', 'etiqueta' => 'Fecha de nacimiento', 'tipo' => 'date'],
            ['nombre' => 'equipo_id', 'etiqueta' => 'Equipo', 'tipo' => 'select', 'opciones' => $equipos],
            ['nombre' => 'imagen', 'etiqueta' => 'Imagen', 'tipo' => 'file'],
            ['nombre' => 'estado', 'etiqueta' => 'Estado', 'tipo' => 'select', 'opciones' => [1 => 'Activo', 0 => 'Inactivo'], 'valorDefecto' => 1],
        ];
    }

    protected function datosValidados(Request $solicitud): array
    {
        return $solicitud->validate([
            'equipo_id' => ['nullable', 'exists:equipos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'imagen' => GestorImagenes::reglas(),
            'estado' => ['required', 'boolean'],
        ]);
    }

    protected function carpetaImagenes(): string
    {
        return $this->tipoPersona() === 'arbitro' ? 'arbitros' : 'jugadores';
    }

    protected function datosPersona(array $datosValidados): array
    {
        return $datosValidados;
    }

    protected function despuesDeGuardarPersona(int $personaId, array $datosValidados): void
    {
    }

    private function formulario(string $tituloFormulario, string $accionFormulario, object $registro = null, string $metodoFormulario = 'POST'): View
    {
        return view('panel.crud.formulario', [
            'tituloFormulario' => $tituloFormulario,
            'accionFormulario' => $accionFormulario,
            'metodoFormulario' => $metodoFormulario,
            'rutaRegreso' => route($this->rutaBase().'.index'),
            'registro' => $registro,
            'campos' => $this->camposFormulario(),
        ]);
    }

    protected function tituloSingular(): string
    {
        return $this->tituloModulo();
    }
}
