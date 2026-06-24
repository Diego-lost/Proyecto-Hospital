<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SpaAuthToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpaHandoffTest extends TestCase
{
    use RefreshDatabase;

    public function test_spa_handoff_redirige_al_front_con_token(): void
    {
        config(['app.frontend_url' => 'http://127.0.0.1:5173/']);

        $admin = User::factory()->admin()->verified()->create();

        $response = $this->actingAs($admin)->get(route('auth.spa-handoff'));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('http://127.0.0.1:5173/?spa_token=', $location);
        $this->assertStringEndsWith('#/', $location);
    }

    public function test_api_me_reconoce_bearer_sin_depender_del_origin(): void
    {
        $user = User::factory()->user()->verified()->create([
            'email' => 'me@local.test',
        ]);

        $token = SpaAuthToken::issue($user);

        $this->withHeaders(['Origin' => 'http://127.0.0.1:5173'])
            ->getJson(route('api.auth.me'), [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('user.email', 'me@local.test');
    }
}
