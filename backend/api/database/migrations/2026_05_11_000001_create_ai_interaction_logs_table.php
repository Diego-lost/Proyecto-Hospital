<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_interaction_logs')) {
            return;
        }

        Schema::create('ai_interaction_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('solicitud_cita_id')->nullable()->constrained('solicitudes_citas')->nullOnDelete();
            $table->string('model', 120);
            $table->char('input_sha256', 64);
            $table->unsignedInteger('input_length');
            $table->json('result')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->boolean('ok')->default(false);
            $table->string('error_code', 64)->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_interaction_logs');
    }
};
