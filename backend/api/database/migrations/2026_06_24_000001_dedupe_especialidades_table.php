<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('especialidades')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $dupes = DB::select('
                SELECT e1.id AS dupe_id, e2.id AS keeper_id
                FROM especialidades e1
                INNER JOIN especialidades e2
                  ON lower(trim(e1.nombre)) = lower(trim(e2.nombre))
                 AND e1.id > e2.id
            ');
        } else {
            $dupes = DB::select("
                SELECT e1.id AS dupe_id, e2.id AS keeper_id
                FROM especialidades e1
                INNER JOIN especialidades e2
                  ON lower(trim(e1.nombre)) = lower(trim(e2.nombre))
                 AND e1.id > e2.id
            ");
        }

        foreach ($dupes as $row) {
            if (Schema::hasTable('medicos')) {
                DB::table('medicos')
                    ->where('especialidad_id', $row->dupe_id)
                    ->update(['especialidad_id' => $row->keeper_id]);
            }

            DB::table('especialidades')->where('id', $row->dupe_id)->delete();
        }
    }

    public function down(): void
    {
        // No reversible: los duplicados eliminados no se restauran.
    }
};
