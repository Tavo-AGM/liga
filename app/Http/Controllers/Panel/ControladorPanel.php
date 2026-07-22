<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ControladorPanel extends Controller
{
    public function index(): View
    {
        $resumenGeneral = [
            'usuarios' => DB::table('usuarios')->count(),
            'ligas' => DB::table('ligas')->count(),
            'equipos' => DB::table('equipos')->count(),
            'jugadores' => DB::table('personas')->where('tipo_persona', 'jugador')->count(),
            'campos' => DB::table('campos')->count(),
            'arbitros' => DB::table('personas')->where('tipo_persona', 'arbitro')->count(),
            'temporadas' => DB::table('temporadas')->count(),
            'torneos' => DB::table('torneos')->count(),
            'partidos' => DB::table('partidos')->count(),
        ];

        $modulosPrincipales = [
            ['nombre' => 'Empresa', 'descripcion' => 'Configura identidad, icono y paleta global.', 'ruta' => route('gestion.empresa.edit')],
            ['nombre' => 'Usuarios', 'descripcion' => 'Crea accesos administrativos.', 'ruta' => route('gestion.usuarios.index')],
            ['nombre' => 'Ligas', 'descripcion' => 'Administra ligas visibles en el portal.', 'ruta' => route('gestion.ligas.index')],
            ['nombre' => 'Equipos', 'descripcion' => 'Gestiona equipos y sus ligas vinculadas.', 'ruta' => route('gestion.equipos.index')],
            ['nombre' => 'Jugadores', 'descripcion' => 'Registra jugadores con o sin equipo.', 'ruta' => route('gestion.jugadores.index')],
            ['nombre' => 'Plantillas', 'descripcion' => 'Relaciona jugadores con equipos por liga y temporada.', 'ruta' => route('gestion.plantillas.index')],
            ['nombre' => 'Traspasos', 'descripcion' => 'Consulta solicitudes de fichaje entre equipos.', 'ruta' => route('gestion.traspasos.index')],
            ['nombre' => 'Campos', 'descripcion' => 'Mantiene campos y ubicaciones.', 'ruta' => route('gestion.campos.index')],
            ['nombre' => 'Arbitros', 'descripcion' => 'Gestiona arbitros disponibles.', 'ruta' => route('gestion.arbitros.index')],
            ['nombre' => 'Temporadas', 'descripcion' => 'Configura temporadas por liga.', 'ruta' => route('gestion.temporadas.index')],
            ['nombre' => 'Partidos', 'descripcion' => 'Edita fecha, hora, campo y estado.', 'ruta' => route('gestion.partidos.index')],
            ['nombre' => 'Generar temporada', 'descripcion' => 'Crea calendarios completos con campos, horarios y arbitros.', 'ruta' => route('gestion.calendarios.temporada.create')],
            ['nombre' => 'Torneos', 'descripcion' => 'Genera torneos tipo liga, mundial, champions o personalizado.', 'ruta' => route('gestion.torneos.generar.create')],
        ];

        $proximasTareas = [
            'Agregar resultados a partidos',
            'Registrar auditoria de cambios',
            'Separar permisos por perfil',
            'Detallar reglas de aprobacion para traspasos',
        ];

        $partidosPorJugar = DB::table('partidos')
            ->leftJoin('temporadas', 'partidos.temporada_id', '=', 'temporadas.id')
            ->leftJoin('torneos', 'partidos.torneo_id', '=', 'torneos.id')
            ->leftJoin('equipos as equipos_locales', 'partidos.equipo_local', '=', 'equipos_locales.id')
            ->leftJoin('equipos as equipos_visitantes', 'partidos.equipo_visitante', '=', 'equipos_visitantes.id')
            ->leftJoin('campos', 'partidos.campo_id', '=', 'campos.id')
            ->leftJoin('personas as arbitros', 'partidos.arbitro', '=', 'arbitros.id')
            ->select(
                'partidos.id',
                'partidos.fecha',
                'partidos.hora',
                'partidos.jornada',
                'partidos.fase',
                'partidos.grupo',
                'partidos.estado',
                'temporadas.nombre as temporada_nombre',
                'torneos.nombre as torneo_nombre',
                'equipos_locales.nombre as equipo_local_nombre',
                'equipos_visitantes.nombre as equipo_visitante_nombre',
                'campos.nombre as campo_nombre',
                'arbitros.nombre as arbitro_nombre',
                'arbitros.apellido_paterno as arbitro_apellido'
            )
            ->whereIn('partidos.estado', ['por_jugar', 'aplazado'])
            ->where(function ($consulta) {
                $consulta->whereNull('partidos.fecha')
                    ->orWhereDate('partidos.fecha', '>=', now()->toDateString());
            })
            ->orderByRaw('partidos.fecha is null')
            ->orderBy('partidos.fecha')
            ->orderBy('partidos.hora')
            ->limit(12)
            ->get();

        return view('panel.dashboard', compact('resumenGeneral', 'modulosPrincipales', 'proximasTareas', 'partidosPorJugar'));
    }
}
