<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicos', function (Blueprint $table) {
            if (! Schema::hasColumn('medicos', 'dni')) {
                $table->string('dni', 20)->nullable()->unique()->after('nombre');
            }
        });

        Schema::table('solicitudes_citas', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes_citas', 'paciente_dni')) {
                $table->string('paciente_dni', 20)->nullable()->after('nombre');
            }
            if (! Schema::hasColumn('solicitudes_citas', 'medico_id')) {
                $table->foreignId('medico_id')->nullable()->after('especialidad')->constrained('medicos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_citas', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes_citas', 'medico_id')) {
                $table->dropConstrainedForeignId('medico_id');
            }
            if (Schema::hasColumn('solicitudes_citas', 'paciente_dni')) {
                $table->dropColumn('paciente_dni');
            }
        });

        Schema::table('medicos', function (Blueprint $table) {
            if (Schema::hasColumn('medicos', 'dni')) {
                $table->dropUnique(['dni']);
                $table->dropColumn('dni');
            }
        });
    }
};
