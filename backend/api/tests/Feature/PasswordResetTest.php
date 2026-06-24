<?php

namespace Tests\Feature;

use App\Models\EmailNotification;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_solicitar_reset_y_guarda_notificacion_en_bd(): void
    {
        Notification::fake();

        $user = User::factory()->user()->verified()->create([
            'email' => 'paciente@local.test',
        ]);

        $response = $this->postJson(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Esperando confirmación en tu correo. Si está registrado, te enviamos un enlace. Revisa tu bandeja de entrada y spam.',
            ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $this->assertDatabaseHas('email_notifications', [
            'email' => $user->email,
            'type' => 'password_reset',
            'status' => 'sent',
            'user_id' => $user->id,
        ]);
    }

    public function test_email_no_registrado_responde_mensaje_generico_sin_notificacion(): void
    {
        Notification::fake();

        $response = $this->postJson(route('password.email'), [
            'email' => 'noexiste@local.test',
        ]);

        $response->assertOk();
        Notification::assertNothingSent();
        $this->assertDatabaseCount('email_notifications', 0);
    }

    public function test_puede_restablecer_contrasena_con_token_valido(): void
    {
        $user = User::factory()->user()->verified()->create([
            'email' => 'reset@local.test',
            'password' => 'old-password',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-99',
            'password_confirmation' => 'new-password-99',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Contraseña actualizada correctamente.']);

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-99', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_token_invalido_rechaza_reset(): void
    {
        $user = User::factory()->user()->verified()->create([
            'email' => 'reset@local.test',
        ]);

        $this->postJson(route('password.update'), [
            'token' => 'token-invalido',
            'email' => $user->email,
            'password' => 'new-password-99',
            'password_confirmation' => 'new-password-99',
        ])->assertStatus(422);

        $this->assertGuest();
    }

    public function test_api_reset_password_devuelve_token_sin_sesion(): void
    {
        $user = User::factory()->user()->verified()->create([
            'email' => 'spa-reset@local.test',
            'password' => 'old-password',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson(route('api.auth.reset-password'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-99',
            'password_confirmation' => 'new-password-99',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Contraseña actualizada correctamente.'])
            ->assertJsonStructure(['user', 'redirect_url', 'token']);

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-99', $user->password));
        $this->assertGuest();
    }
}
