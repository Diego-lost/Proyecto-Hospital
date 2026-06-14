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
            if (! Schema::hasColumn('pagos', 'metodo')) {
                $table->string('metodo', 24)->default('tarjeta')->after('moneda');
            }
            if (! Schema::hasColumn('pagos', 'referencia_manual')) {
                $table->string('referencia_manual', 120)->nullable()->after('stripe_checkout_session_id');
            }
            if (! Schema::hasColumn('pagos', 'notas')) {
                $table->text('notas')->nullable()->after('referencia_manual');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pagos')) {
            return;
        }

        Schema::table('pagos', function (Blueprint $table) {
            if (Schema::hasColumn('pagos', 'notas')) {
                $table->dropColumn('notas');
            }
            if (Schema::hasColumn('pagos', 'referencia_manual')) {
                $table->dropColumn('referencia_manual');
            }
            if (Schema::hasColumn('pagos', 'metodo')) {
                $table->dropColumn('metodo');
            }
        });
    }
};
