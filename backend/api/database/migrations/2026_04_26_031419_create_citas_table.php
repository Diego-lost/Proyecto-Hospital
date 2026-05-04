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
    if (Schema::hasTable('citas')) {
        return;
    }

    Schema::create('citas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('servicio_id')->constrained()->onDelete('cascade');
        $table->date('fecha');
        $table->time('hora');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
