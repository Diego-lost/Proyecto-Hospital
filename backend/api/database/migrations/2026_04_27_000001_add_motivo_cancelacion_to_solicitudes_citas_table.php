<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('solicitudes_citas')) {
            return;
        }

        Schema::table('solicitudes_citas', function (Blueprint $table) {
            if (Schema::hasColumn('solicitudes_citas', 'motivo_cancelacion')) {
                return;
            }

            $table->text('motivo_cancelacion')->nullable()->after('motivo');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('solicitudes_citas')) {
            return;
        }

        Schema::table('solicitudes_citas', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitudes_citas', 'motivo_cancelacion')) {
                return;
            }

            $table->dropColumn('motivo_cancelacion');
        });
    }
};

