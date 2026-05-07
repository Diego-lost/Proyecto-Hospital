<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFrontendLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_ver_sitio_web_en_admin_apunta_al_frontend_en_subdirectorio(): void
    {
        config(['app.frontend_url' => null]);
        config(['app.url' => 'http://localhost']);

        $response = $this->actingAs($this->devPanelUser())->call('GET', '/admin', [], [], [], [
            'SCRIPT_NAME' => '/ProyectoNuevo/backend/api/public/index.php',
            'PHP_SELF' => '/ProyectoNuevo/backend/api/public/index.php',
            'HTTP_HOST' => 'localhost',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
        ]);

        $response->assertOk();
        $response->assertSee('href="'.route('web.public').'"', false);
    }
}
