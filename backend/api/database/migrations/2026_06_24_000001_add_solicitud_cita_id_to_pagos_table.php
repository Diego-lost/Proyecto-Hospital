<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pagos')) {
            return;
        }

        Schema::table('pagos', function (Blueprint $table) {
            if (! Schema::hasColumn('pagos', 'solicitud_cita_id')) {
                $table->foreignId('solicitud_cita_id')
                    ->nullable()
                    ->after('servicio_id')
                    ->constrained('solicitudes_citas')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pagos')) {
            return;
        }

        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'solicitud_cita_id')) {
                $table->dropConstrainedForeignId('solicitud_cita_id');
            }
        });
    }
};
