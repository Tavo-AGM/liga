<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuario_tipo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_usuario')->nullable()->constrained('usuario_tipo')->nullOnDelete();
            $table->unsignedBigInteger('persona_id')->nullable();
            $table->unsignedBigInteger('equipo_id')->nullable();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('contrasena');
            $table->text('token')->nullable();
            $table->date('fecha_creacion')->nullable();
            $table->string('imagen')->nullable();
            $table->boolean('estado')->default(true);
            $table->boolean('es_usuario_sistema')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('usuario_tipo');
    }
};
