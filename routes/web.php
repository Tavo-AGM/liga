<?php

use App\Http\Controllers\Arbitros\ControladorPanelArbitro;
use App\Http\Controllers\Calendarios\ControladorCalendarioTemporada;
use App\Http\Controllers\Campos\ControladorCampo;
use App\Http\Controllers\Encargados\ControladorPanelEncargado;
use App\Http\Controllers\Empresa\ControladorEmpresa;
use App\Http\Controllers\Equipos\ControladorEquipo;
use App\Http\Controllers\Ligas\ControladorLiga;
use App\Http\Controllers\Panel\ControladorPanel;
use App\Http\Controllers\Partidos\ControladorPartido;
use App\Http\Controllers\Personas\ControladorArbitro;
use App\Http\Controllers\Personas\ControladorJugador;
use App\Http\Controllers\Publico\ControladorInicio;
use App\Http\Controllers\Sesiones\ControladorSesion;
use App\Http\Controllers\Temporadas\ControladorTemporada;
use App\Http\Controllers\Torneos\ControladorGeneradorTorneo;
use App\Http\Controllers\Plantillas\ControladorPlantilla;
use App\Http\Controllers\Traspasos\ControladorTraspaso;
use App\Http\Controllers\Usuarios\ControladorUsuario;
use Illuminate\Support\Facades\Route;

Route::get('/', [ControladorInicio::class, 'index'])->name('publico.index');
Route::get('/temporadas/{temporada}/tabla', [ControladorInicio::class, 'tablaTemporada'])->name('publico.temporadas.tabla');

Route::middleware('guest')->group(function () {
    Route::get('/login', [ControladorSesion::class, 'mostrarFormulario'])->name('sesiones.login');
    Route::post('/login', [ControladorSesion::class, 'iniciarSesion'])->name('sesiones.iniciar');
});

Route::middleware('auth')->group(function () {
    Route::get('/panel', [ControladorPanel::class, 'index'])->middleware('tipo.usuario:Administrador')->name('panel.dashboard');
    Route::get('/arbitro/panel', [ControladorPanelArbitro::class, 'index'])->middleware('tipo.usuario:Arbitro')->name('arbitros.panel');
    Route::get('/equipo/panel', [ControladorPanelEncargado::class, 'index'])->middleware('tipo.usuario:Encargado de equipo')->name('encargados.panel');
    Route::post('/equipo/traspasos', [ControladorTraspaso::class, 'store'])->middleware('tipo.usuario:Encargado de equipo')->name('encargados.traspasos.store');
    Route::patch('/equipo/traspasos/{traspaso}', [ControladorTraspaso::class, 'responder'])->middleware('tipo.usuario:Encargado de equipo')->name('encargados.traspasos.responder');
    Route::post('/logout', [ControladorSesion::class, 'cerrarSesion'])->name('sesiones.cerrar');

    Route::prefix('gestion')->name('gestion.')->middleware('tipo.usuario:Administrador')->group(function () {
        Route::resource('usuarios', ControladorUsuario::class)->except(['show']);
        Route::resource('ligas', ControladorLiga::class)->except(['show']);
        Route::resource('equipos', ControladorEquipo::class)->except(['show']);
        Route::resource('jugadores', ControladorJugador::class)->parameters(['jugadores' => 'persona'])->except(['show']);
        Route::resource('campos', ControladorCampo::class)->except(['show']);
        Route::resource('arbitros', ControladorArbitro::class)->parameters(['arbitros' => 'persona'])->except(['show']);
        Route::resource('temporadas', ControladorTemporada::class)->except(['show']);
        Route::resource('partidos', ControladorPartido::class)->only(['index', 'edit', 'update']);
        Route::resource('plantillas', ControladorPlantilla::class)->except(['show']);
        Route::get('traspasos', [ControladorTraspaso::class, 'index'])->name('traspasos.index');
        Route::get('empresa', [ControladorEmpresa::class, 'edit'])->name('empresa.edit');
        Route::put('empresa', [ControladorEmpresa::class, 'update'])->name('empresa.update');
        Route::post('empresa/reiniciar-visitas', [ControladorEmpresa::class, 'reiniciarVisitas'])->name('empresa.reiniciar-visitas');
        Route::get('calendarios/temporada', [ControladorCalendarioTemporada::class, 'create'])->name('calendarios.temporada.create');
        Route::post('calendarios/temporada', [ControladorCalendarioTemporada::class, 'store'])->name('calendarios.temporada.store');
        Route::get('torneos/generar', [ControladorGeneradorTorneo::class, 'create'])->name('torneos.generar.create');
        Route::post('torneos/generar', [ControladorGeneradorTorneo::class, 'store'])->name('torneos.generar.store');
    });
});
