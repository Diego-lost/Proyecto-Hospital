<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class HomeRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_muestra_fallback_cuando_coincide_con_el_frontend_resuelto(): void
    {
        config(['app.url' => 'http://localhost']);
        config(['app.frontend_url' => null]);
        URL::forceRootUrl('http://localhost');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Clínica NovaSalud', false);
    }

    public function test_home_respeta_frontend_url_explicito(): void
    {
        config([
            'app.url' => 'http://localhost',
            'app.frontend_url' => 'http://ejemplo.test/clinica/',
        ]);
        URL::forceRootUrl('http://localhost');

        $this->get(route('home'))
            ->assertRedirect('http://ejemplo.test/clinica/');
    }
}
