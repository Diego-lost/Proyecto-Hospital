<?php

namespace Tests\Feature;

use App\Models\SolicitudCita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSolicitudCitaIndexQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_query_eager_loads_pago_without_ambiguous_column_error(): void
    {
        SolicitudCita::query()->create([
            'nombre' => 'Paciente Test',
            'telefono' => '999999999',
            'email' => 'paciente@example.com',
            'especialidad' => 'Medicina General',
            'fecha' => now()->toDateString(),
            'hora' => '10:00',
            'estado' => 'pendiente',
        ]);

        $solicitudes = SolicitudCita::query()
            ->with([
                'medico:id,nombre,dni',
                'pago' => function ($query) {
                    $query->select(
                        'pagos.id',
                        'pagos.solicitud_cita_id',
                        'pagos.estado',
                        'pagos.metodo',
                        'pagos.monto',
                        'pagos.moneda',
                        'pagos.paid_at',
                    );
                },
            ])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $this->assertCount(1, $solicitudes);
    }
}
