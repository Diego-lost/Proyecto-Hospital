<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pagos')) {
            return;
        }

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->string('cliente_nombre', 120);
            $table->string('cliente_email', 160);
            $table->string('cliente_telefono', 40)->nullable();
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 3)->default('pen');
            $table->string('metodo', 24);
            $table->string('estado', 32)->default('pending');
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('referencia_manual', 120)->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
            $table->index('metodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
