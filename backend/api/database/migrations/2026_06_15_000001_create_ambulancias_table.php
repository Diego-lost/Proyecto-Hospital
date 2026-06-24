<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ambulancias')) {
            return;
        }

        Schema::create('ambulancias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('placa', 20)->nullable();
            $table->string('conductor', 120)->nullable();
            $table->string('estado', 20)->default('disponible');
            $table->decimal('origen_lat', 10, 7)->nullable();
            $table->decimal('origen_lng', 10, 7)->nullable();
            $table->decimal('destino_lat', 10, 7)->nullable();
            $table->decimal('destino_lng', 10, 7)->nullable();
            $table->string('destino_direccion', 500)->nullable();
            $table->unsignedInteger('distancia_metros')->nullable();
            $table->unsignedInteger('duracion_segundos')->nullable();
            $table->text('ruta_resumen')->nullable();
            $table->timestamp('despachada_at')->nullable();
            $table->timestamp('regreso_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambulancias');
    }
};
