<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_citas', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes_citas', 'triage_riesgo')) {
                $table->string('triage_riesgo', 16)->nullable()->after('motivo');
            }
            if (! Schema::hasColumn('solicitudes_citas', 'triage_accion')) {
                $table->string('triage_accion', 32)->nullable()->after('triage_riesgo');
            }
            if (! Schema::hasColumn('solicitudes_citas', 'triage_resumen')) {
                $table->json('triage_resumen')->nullable()->after('triage_accion');
            }
            if (! Schema::hasColumn('solicitudes_citas', 'prioridad')) {
                $table->string('prioridad', 16)->default('normal')->after('estado');
            }
            if (! Schema::hasColumn('solicitudes_citas', 'seguimiento_mensaje')) {
                $table->string('seguimiento_mensaje', 500)->nullable()->after('prioridad');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_citas', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes_citas', 'seguimiento_mensaje')) {
                $table->dropColumn('seguimiento_mensaje');
            }
            if (Schema::hasColumn('solicitudes_citas', 'prioridad')) {
                $table->dropColumn('prioridad');
            }
            if (Schema::hasColumn('solicitudes_citas', 'triage_resumen')) {
                $table->dropColumn('triage_resumen');
            }
            if (Schema::hasColumn('solicitudes_citas', 'triage_accion')) {
                $table->dropColumn('triage_accion');
            }
            if (Schema::hasColumn('solicitudes_citas', 'triage_riesgo')) {
                $table->dropColumn('triage_riesgo');
            }
        });
    }
};
