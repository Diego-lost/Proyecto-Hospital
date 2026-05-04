<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('solicitudes_citas')) {
            return;
        }

        Schema::create('solicitudes_citas', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('telefono');
            $table->string('email')->nullable();
            $table->string('especialidad')->nullable();
            $table->date('fecha')->nullable();
            $table->time('hora')->nullable();
            $table->text('motivo')->nullable();
            $table->text('motivo_cancelacion')->nullable();

            $table->string('estado')->default('nueva');
            $table->string('origen')->default('web');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_citas');
    }
};

