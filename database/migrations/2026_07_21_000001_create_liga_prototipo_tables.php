<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('eslogan')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('texto_pie_pagina')->nullable();
            $table->string('imagen')->nullable();
            $table->string('color_primario', 7)->default('#047857');
            $table->string('color_secundario', 7)->default('#0f172a');
            $table->string('color_acento', 7)->default('#10b981');
            $table->string('color_fondo', 7)->default('#f4f4f5');
            $table->string('color_texto', 7)->default('#ffffff');
            $table->string('color_menu', 7)->default('#ffffff');
            $table->string('color_pie_pagina', 7)->default('#ffffff');
            $table->unsignedBigInteger('visitas')->default(0);
            $table->timestamps();
        });

        Schema::create('ligas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('eslogan')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->timestamps();
        });

        Schema::create('temporadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liga_id')->nullable()->constrained('ligas')->nullOnDelete();
            $table->string('nombre');
            $table->date('desde')->nullable();
            $table->date('hasta')->nullable();
            $table->timestamps();
        });

        Schema::create('campos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubicacion')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campo_id')->nullable()->constrained('campos')->nullOnDelete();
            $table->string('nombre');
            $table->string('imagen')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('equipo_liga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->foreignId('liga_id')->constrained('ligas')->cascadeOnDelete();
            $table->string('nombre_en_liga')->nullable();
            $table->string('imagen_en_liga')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->unique(['equipo_id', 'liga_id']);
        });

        Schema::create('usuario_equipo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->unique(['usuario_id', 'equipo_id']);
        });

        Schema::create('torneos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temporada_id')->nullable()->constrained('temporadas')->nullOnDelete();
            $table->string('nombre');
            $table->string('tipo')->default('personalizado');
            $table->json('reglas')->nullable();
            $table->string('estado')->default('borrador');
            $table->timestamps();
        });

        Schema::create('torneo_equipo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('torneo_id')->constrained('torneos')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['torneo_id', 'equipo_id']);
        });

        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->string('tipo_persona')->default('jugador');
            $table->string('nombre');
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('imagen')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        Schema::create('jugador_equipo_liga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->foreignId('liga_id')->nullable()->constrained('ligas')->nullOnDelete();
            $table->foreignId('temporada_id')->nullable()->constrained('temporadas')->nullOnDelete();
            $table->date('fecha_alta')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->string('estado')->default('activo');
            $table->string('origen')->default('registro');
            $table->timestamps();
            $table->index(['persona_id', 'liga_id', 'temporada_id', 'estado']);
        });

        Schema::create('traspasos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jugador_id')->constrained('personas')->cascadeOnDelete();
            $table->foreignId('equipo_origen_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->foreignId('liga_origen_id')->nullable()->constrained('ligas')->nullOnDelete();
            $table->foreignId('equipo_destino_id')->constrained('equipos')->cascadeOnDelete();
            $table->foreignId('liga_destino_id')->nullable()->constrained('ligas')->nullOnDelete();
            $table->foreignId('temporada_id')->nullable()->constrained('temporadas')->nullOnDelete();
            $table->foreignId('usuario_solicitante_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('usuario_receptor_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('estado')->default('pendiente');
            $table->text('mensaje')->nullable();
            $table->text('respuesta')->nullable();
            $table->timestamp('respondido_en')->nullable();
            $table->timestamps();
            $table->index(['equipo_origen_id', 'estado']);
            $table->index(['equipo_destino_id', 'estado']);
        });

        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->nullable();
            $table->time('hora')->nullable();
            $table->string('estado')->default('por_jugar');
            $table->foreignId('campo_id')->nullable()->constrained('campos')->nullOnDelete();
            $table->foreignId('liga_id')->nullable()->constrained('ligas')->nullOnDelete();
            $table->foreignId('temporada_id')->nullable()->constrained('temporadas')->nullOnDelete();
            $table->foreignId('torneo_id')->nullable()->constrained('torneos')->nullOnDelete();
            $table->foreignId('equipo_local')->nullable()->constrained('equipos')->nullOnDelete();
            $table->foreignId('equipo_visitante')->nullable()->constrained('equipos')->nullOnDelete();
            $table->foreignId('arbitro')->nullable()->constrained('personas')->nullOnDelete();
            $table->unsignedSmallInteger('goles_local')->nullable();
            $table->unsignedSmallInteger('goles_visitante')->nullable();
            $table->unsignedSmallInteger('jornada')->nullable();
            $table->string('fase')->nullable();
            $table->string('grupo')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->index(['campo_id', 'fecha', 'hora']);
        });

        Schema::create('partido_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tiempo')->nullable();
            $table->string('estado')->nullable();
            $table->foreignId('partido_id')->constrained('partidos')->cascadeOnDelete();
            $table->foreignId('persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreign('persona_id')->references('id')->on('personas')->nullOnDelete();
            $table->foreign('equipo_id')->references('id')->on('equipos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropForeign(['equipo_id']);
        });

        Schema::dropIfExists('partido_eventos');
        Schema::dropIfExists('partidos');
        Schema::dropIfExists('traspasos');
        Schema::dropIfExists('jugador_equipo_liga');
        Schema::dropIfExists('personas');
        Schema::dropIfExists('torneo_equipo');
        Schema::dropIfExists('torneos');
        Schema::dropIfExists('usuario_equipo');
        Schema::dropIfExists('equipo_liga');
        Schema::dropIfExists('equipos');
        Schema::dropIfExists('campos');
        Schema::dropIfExists('temporadas');
        Schema::dropIfExists('ligas');
        Schema::dropIfExists('empresa');
    }
};
