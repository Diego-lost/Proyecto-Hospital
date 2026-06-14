<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            return response('Webhook no configurado (STRIPE_WEBHOOK_SECRET).', 501);
        }

        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            Log::warning('Stripe webhook rechazado', ['error' => $e->getMessage()]);

            return response('Firma inválida.', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $sessionId = $session->id ?? null;

            if (is_string($sessionId) && $sessionId !== '') {
                $pago = Pago::query()
                    ->where('stripe_checkout_session_id', $sessionId)
                    ->first();

                if ($pago !== null && $pago->estado !== Pago::ESTADO_PAID) {
                    $pago->marcarPagado('Confirmado vía webhook Stripe.');
                }
            }
        }

        return response('OK', 200);
    }
}
