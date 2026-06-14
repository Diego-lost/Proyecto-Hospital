<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_citas', function (Blueprint $table) {
            if (! Schema::hasColumn('solicitudes_citas', 'paciente_direccion')) {
                $table->string('paciente_direccion', 500)->nullable()->after('paciente_dni');
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_citas', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes_citas', 'paciente_direccion')) {
                $table->dropColumn('paciente_direccion');
            }
        });
    }
};
