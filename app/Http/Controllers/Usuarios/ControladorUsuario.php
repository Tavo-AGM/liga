<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Support\GestorImagenes;

class ControladorUsuario extends Controller
{
    public function index(): View
    {
        $registros = DB::table('usuarios')
            ->leftJoin('usuario_tipo', 'usuarios.tipo_usuario', '=', 'usuario_tipo.id')
            ->leftJoin('personas', 'usuarios.persona_id', '=', 'personas.id')
            ->leftJoin('equipos', 'usuarios.equipo_id', '=', 'equipos.id')
            ->select(
                'usuarios.id',
                'usuarios.nombre',
                'usuarios.email',
                'usuarios.imagen',
                'usuarios.estado',
                'usuarios.es_usuario_sistema',
                'usuario_tipo.nombre as tipo_nombre',
                'personas.nombre as persona_nombre',
                'equipos.nombre as equipo_nombre'
            )
            ->orderBy('usuarios.nombre')
            ->paginate(10);

        $columnas = [
            ['campo' => 'nombre', 'etiqueta' => 'Nombre'],
            ['campo' => 'imagen', 'etiqueta' => 'Imagen', 'tipo' => 'imagen'],
            ['campo' => 'email', 'etiqueta' => 'Correo'],
            ['campo' => 'tipo_nombre', 'etiqueta' => 'Tipo'],
            ['campo' => 'persona_nombre', 'etiqueta' => 'Persona'],
            ['campo' => 'equipos_vinculados', 'etiqueta' => 'Equipos'],
            ['campo' => 'estadoTexto', 'etiqueta' => 'Estado'],
        ];

        $registros->getCollection()->transform(function ($usuario) {
            $usuario->estadoTexto = $usuario->estado ? 'Activo' : 'Inactivo';
            $usuario->puedeEliminar = ! $usuario->es_usuario_sistema;
            $equiposAdicionales = DB::table('usuario_equipo')
                ->join('equipos', 'usuario_equipo.equipo_id', '=', 'equipos.id')
                ->where('usuario_equipo.usuario_id', $usuario->id)
                ->orderBy('equipos.nombre')
                ->pluck('equipos.nombre')
                ->toArray();

            $usuario->equipos_vinculados = implode(', ', array_unique(array_filter(array_merge([$usuario->equipo_nombre], $equiposAdicionales))));

            return $usuario;
        });

        return view('panel.crud.index', [
            'tituloModulo' => 'Usuarios',
            'descripcionModulo' => 'Administra los accesos al panel.',
            'registros' => $registros,
            'columnas' => $columnas,
            'rutaCrear' => route('gestion.usuarios.create'),
            'rutaEditarNombre' => 'gestion.usuarios.edit',
            'rutaEliminarNombre' => 'gestion.usuarios.destroy',
        ]);
    }

    public function create(): View
    {
        return $this->formulario('Crear usuario', route('gestion.usuarios.store'));
    }

    public function store(Request $solicitud): RedirectResponse
    {
        $datosValidados = $solicitud->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:usuarios,email'],
            'contrasena' => ['required', 'string', 'min:4'],
            'tipo_usuario' => ['nullable', 'exists:usuario_tipo,id'],
            'persona_id' => ['nullable', 'exists:personas,id'],
            'equipo_id' => ['nullable', 'exists:equipos,id'],
            'equipos' => ['nullable', 'array'],
            'equipos.*' => ['exists:equipos,id'],
            'imagen' => GestorImagenes::reglas(),
            'estado' => ['required', 'boolean'],
        ]);

        $equiposVinculados = $datosValidados['equipos'] ?? [];
        unset($datosValidados['equipos']);

        $datosValidados['imagen'] = GestorImagenes::guardar($solicitud, 'usuarios');
        $datosValidados['contrasena'] = Hash::make($datosValidados['contrasena']);
        $datosValidados['fecha_creacion'] = now()->toDateString();
        $datosValidados['created_at'] = now();
        $datosValidados['updated_at'] = now();

        $usuarioId = DB::table('usuarios')->insertGetId($datosValidados);
        $this->sincronizarEquipos($usuarioId, $datosValidados['equipo_id'] ?? null, $equiposVinculados);

        return redirect()->route('gestion.usuarios.index')->with('mensaje', 'Usuario creado correctamente.');
    }

    public function edit(int $usuario): View
    {
        $registro = DB::table('usuarios')->where('id', $usuario)->first();
        abort_if(! $registro, 404);

        return $this->formulario('Editar usuario', route('gestion.usuarios.update', $usuario), $registro, 'PUT');
    }

    public function update(Request $solicitud, int $usuario): RedirectResponse
    {
        $usuarioActual = DB::table('usuarios')->where('id', $usuario)->first();
        abort_if(! $usuarioActual, 404);

        $datosValidados = $solicitud->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($usuario)],
            'contrasena' => ['nullable', 'string', 'min:4'],
            'tipo_usuario' => ['nullable', 'exists:usuario_tipo,id'],
            'persona_id' => ['nullable', 'exists:personas,id'],
            'equipo_id' => ['nullable', 'exists:equipos,id'],
            'equipos' => ['nullable', 'array'],
            'equipos.*' => ['exists:equipos,id'],
            'imagen' => GestorImagenes::reglas(),
            'estado' => ['required', 'boolean'],
        ]);

        $equiposVinculados = $datosValidados['equipos'] ?? [];
        unset($datosValidados['equipos']);

        if (! empty($datosValidados['contrasena'])) {
            $datosValidados['contrasena'] = Hash::make($datosValidados['contrasena']);
        } else {
            unset($datosValidados['contrasena']);
        }

        $datosValidados['imagen'] = GestorImagenes::guardar($solicitud, 'usuarios', $usuarioActual->imagen);
        $datosValidados['updated_at'] = now();

        DB::table('usuarios')->where('id', $usuario)->update($datosValidados);
        $this->sincronizarEquipos($usuario, $datosValidados['equipo_id'] ?? null, $equiposVinculados);

        return redirect()->route('gestion.usuarios.index')->with('mensaje', 'Usuario actualizado correctamente.');
    }

    public function destroy(int $usuario): RedirectResponse
    {
        $usuarioActual = DB::table('usuarios')->where('id', $usuario)->first();

        if ($usuarioActual?->es_usuario_sistema) {
            return redirect()->route('gestion.usuarios.index')->with('mensaje', 'El usuario administrador por defecto no se puede eliminar.');
        }

        GestorImagenes::eliminar($usuarioActual?->imagen);

        DB::table('usuarios')->where('id', $usuario)->delete();

        return redirect()->route('gestion.usuarios.index')->with('mensaje', 'Usuario eliminado correctamente.');
    }

    private function formulario(string $tituloFormulario, string $accionFormulario, object $registro = null, string $metodoFormulario = 'POST'): View
    {
        $tiposUsuario = DB::table('usuario_tipo')->orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $personas = DB::table('personas')
            ->select('id', DB::raw("trim(concat(nombre, ' ', coalesce(apellido_paterno, ''), ' ', coalesce(apellido_materno, ''))) as nombre_completo"))
            ->orderBy('nombre')
            ->pluck('nombre_completo', 'id')
            ->toArray();
        $equipos = DB::table('equipos')->orderBy('nombre')->pluck('nombre', 'id')->toArray();
        $equiposSeleccionados = $registro
            ? DB::table('usuario_equipo')->where('usuario_id', $registro->id)->pluck('equipo_id')->map(fn ($equipoId) => (string) $equipoId)->toArray()
            : [];

        return view('panel.crud.formulario', [
            'tituloFormulario' => $tituloFormulario,
            'accionFormulario' => $accionFormulario,
            'metodoFormulario' => $metodoFormulario,
            'rutaRegreso' => route('gestion.usuarios.index'),
            'registro' => $registro,
            'campos' => [
                ['nombre' => 'nombre', 'etiqueta' => 'Nombre', 'tipo' => 'text', 'requerido' => true],
                ['nombre' => 'email', 'etiqueta' => 'Correo', 'tipo' => 'email', 'requerido' => true],
                ['nombre' => 'contrasena', 'etiqueta' => 'Contrasena', 'tipo' => 'password', 'requerido' => $metodoFormulario === 'POST'],
                ['nombre' => 'tipo_usuario', 'etiqueta' => 'Tipo de usuario', 'tipo' => 'select', 'opciones' => $tiposUsuario],
                ['nombre' => 'persona_id', 'etiqueta' => 'Persona vinculada', 'tipo' => 'select', 'opciones' => $personas],
                ['nombre' => 'equipo_id', 'etiqueta' => 'Equipo principal', 'tipo' => 'select', 'opciones' => $equipos],
                ['nombre' => 'equipos', 'etiqueta' => 'Equipos administrados', 'tipo' => 'multiselect', 'opciones' => $equipos, 'valoresSeleccionados' => $equiposSeleccionados],
                ['nombre' => 'imagen', 'etiqueta' => 'Imagen', 'tipo' => 'file'],
                ['nombre' => 'estado', 'etiqueta' => 'Estado', 'tipo' => 'select', 'opciones' => [1 => 'Activo', 0 => 'Inactivo'], 'valorDefecto' => 1],
            ],
        ]);
    }

    private function sincronizarEquipos(int $usuarioId, ?int $equipoPrincipalId, array $equiposVinculados): void
    {
        DB::table('usuario_equipo')->where('usuario_id', $usuarioId)->delete();

        $equipos = array_filter(array_unique(array_merge($equiposVinculados, [$equipoPrincipalId])));

        foreach ($equipos as $equipoId) {
            DB::table('usuario_equipo')->insert([
                'usuario_id' => $usuarioId,
                'equipo_id' => $equipoId,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
