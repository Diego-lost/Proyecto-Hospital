<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_registrarse_y_recibe_notificacion_por_correo(): void
    {
        Notification::fake();

        $response = $this->postJson(route('register'), [
            'name' => 'María Paciente',
            'email' => 'maria@local.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['pending_verification' => true])
            ->assertJsonStructure(['user', 'redirect_url']);

        $this->assertAuthenticated();
        $user = User::query()->where('email', 'maria@local.test')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_registro_rechaza_email_duplicado(): void
    {
        User::factory()->create(['email' => 'duplicado@local.test']);

        $this->postJson(route('register'), [
            'name' => 'Otro Usuario',
            'email' => 'duplicado@local.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ])->assertStatus(422);

        $this->assertGuest();
    }
}
