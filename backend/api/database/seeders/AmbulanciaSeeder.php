<?php

namespace Database\Seeders;

use App\Models\Ambulancia;
use Illuminate\Database\Seeder;

class AmbulanciaSeeder extends Seeder
{
    public function run(): void
    {
        $origen = [
            'origen_lat' => (float) config('services.google_maps.origin_lat', -12.0464),
            'origen_lng' => (float) config('services.google_maps.origin_lng', -77.0428),
        ];

        $unidades = [
            ['codigo' => 'AMB-LIM-01', 'placa' => 'BCP-201', 'conductor' => 'Juan Paredes Ríos'],
            ['codigo' => 'AMB-LIM-02', 'placa' => 'BCP-202', 'conductor' => 'Miguel Salazar Vega'],
            ['codigo' => 'AMB-LIM-03', 'placa' => 'BCP-203', 'conductor' => 'Carlos Mendoza López'],
            ['codigo' => 'AMB-LIM-04', 'placa' => 'BCP-204', 'conductor' => 'Luis Ramírez Paredes'],
            ['codigo' => 'AMB-LIM-05', 'placa' => 'BCP-205', 'conductor' => 'Pedro Aguilar Vela'],
        ];

        foreach ($unidades as $unidad) {
            Ambulancia::query()->firstOrCreate(
                ['codigo' => $unidad['codigo']],
                [
                    'placa' => $unidad['placa'],
                    'conductor' => $unidad['conductor'],
                    'estado' => Ambulancia::ESTADO_DISPONIBLE,
                    'origen_lat' => $origen['origen_lat'],
                    'origen_lng' => $origen['origen_lng'],
                ],
            );
        }
    }
}
