<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
   {
    if (Schema::hasTable('medicos')) {
        return;
    }

    Schema::create('medicos', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
        $table->string('foto')->nullable();
        $table->timestamps();
    });
   }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};
