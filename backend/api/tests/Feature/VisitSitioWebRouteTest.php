<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitSitioWebRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitio_web_redirige_a_frontend_explicito_en_config(): void
    {
        config(['app.frontend_url' => 'https://clinica.example/publico/']);

        $this->get(route('web.public'))
            ->assertRedirect('https://clinica.example/publico/');
    }

    public function test_sitio_web_redirige_a_frontend_deducido_en_xampp(): void
    {
        config(['app.frontend_url' => null]);
        config(['app.url' => 'http://localhost']);

        $response = $this->call('GET', '/sitio-web', [], [], [], [
            'SCRIPT_NAME' => '/ProyectoNuevo/backend/api/public/index.php',
            'PHP_SELF' => '/ProyectoNuevo/backend/api/public/index.php',
            'HTTP_HOST' => 'localhost',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
        ]);

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringEndsWith('/ProyectoNuevo/backend/api/public/clinica/', $location);
    }
}
