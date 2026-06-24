<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Servicio;
use App\Support\FrontendPagoUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class PagoController extends Controller
{
    public function config(): JsonResponse
    {
        $secret = config('services.stripe.secret');

        return response()->json([
            'stripe_configured' => is_string($secret) && $secret !== '',
            'currency' => config('services.stripe.currency', 'pen'),
            'public_key' => config('services.stripe.public'),
            'admin_fee' => (float) config('pagos.admin_fee', 0),
            'manual' => [
                'yape_phone' => config('pagos.yape_phone'),
                'pagos_email' => config('pagos.pagos_email'),
                'bank' => config('pagos.bank'),
            ],
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $secret = config('services.stripe.secret');
        if (! is_string($secret) || $secret === '') {
            return response()->json([
                'message' => 'Los pagos con tarjeta no están configurados (STRIPE_SECRET).',
            ], 503);
        }

        $data = $request->validate([
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'solicitud_cita_id' => ['nullable', 'integer', 'exists:solicitudes_citas,id'],
            'cliente_nombre' => ['required', 'string', 'max:120'],
            'cliente_email' => ['required', 'email', 'max:160'],
            'cliente_telefono' => ['nullable', 'string', 'max:40'],
        ]);

        $servicio = Servicio::query()->findOrFail($data['servicio_id']);
        $monto = round((float) $servicio->precio, 2);
        if ($monto <= 0) {
            return response()->json([
                'message' => 'El servicio seleccionado no tiene un precio válido para cobrar.',
            ], 422);
        }

        $adminFee = round((float) config('pagos.admin_fee', 0), 2);
        $total = round($monto + $adminFee, 2);
        $currency = strtolower((string) config('services.stripe.currency', 'pen'));

        $pago = Pago::query()->create([
            'servicio_id' => $servicio->id,
            'solicitud_cita_id' => $data['solicitud_cita_id'] ?? null,
            'cliente_nombre' => $data['cliente_nombre'],
            'cliente_email' => $data['cliente_email'],
            'cliente_telefono' => $data['cliente_telefono'] ?? null,
            'monto' => $total,
            'moneda' => $currency,
            'metodo' => Pago::METODO_TARJETA,
            'estado' => Pago::ESTADO_PENDING,
        ]);

        $solicitudCitaId = $data['solicitud_cita_id'] ?? null;
        $successUrl = ($solicitudCitaId !== null)
            ? FrontendPagoUrl::exitoCitaStripe((int) $solicitudCitaId)
            : FrontendPagoUrl::exitoStripe();

        try {
            Stripe::setApiKey($secret);

            $metadata = [
                'pago_id' => (string) $pago->id,
                'servicio_id' => (string) $servicio->id,
            ];
            if ($solicitudCitaId !== null) {
                $metadata['solicitud_cita_id'] = (string) $solicitudCitaId;
            }

            $session = StripeCheckoutSession::create([
                'mode' => 'payment',
                'customer_email' => $data['cliente_email'],
                'client_reference_id' => (string) $pago->id,
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => self::toStripeAmount($total, $currency),
                        'product_data' => [
                            'name' => $servicio->nombre,
                            'description' => Str::limit((string) $servicio->descripcion, 500) ?: null,
                        ],
                    ],
                ]],
                'success_url' => $successUrl,
                'cancel_url' => FrontendPagoUrl::canceladoStripe(),
                'metadata' => $metadata,
            ]);
        } catch (ApiErrorException $e) {
            $pago->estado = Pago::ESTADO_CANCELLED;
            $pago->notas = 'Error Stripe: '.$e->getMessage();
            $pago->save();

            return response()->json([
                'message' => 'No se pudo iniciar el pago con Stripe.',
                'detail' => app()->isLocal() ? $e->getMessage() : null,
            ], 502);
        }

        $pago->stripe_checkout_session_id = $session->id;
        $pago->save();

        return response()->json([
            'checkout_url' => $session->url,
            'session_id' => $session->id,
            'pago_id' => $pago->id,
        ]);
    }

    public function manual(Request $request): JsonResponse
    {
        $data = $request->validate([
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'solicitud_cita_id' => ['nullable', 'integer', 'exists:solicitudes_citas,id'],
            'cliente_nombre' => ['required', 'string', 'max:120'],
            'cliente_email' => ['required', 'email', 'max:160'],
            'cliente_telefono' => ['nullable', 'string', 'max:40'],
            'metodo' => ['required', 'string', 'in:yape,transferencia'],
            'referencia_manual' => ['nullable', 'string', 'max:120'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ]);

        $servicio = Servicio::query()->findOrFail($data['servicio_id']);
        $monto = round((float) $servicio->precio, 2);
        if ($monto <= 0) {
            return response()->json([
                'message' => 'El servicio seleccionado no tiene un precio válido.',
            ], 422);
        }

        $adminFee = round((float) config('pagos.admin_fee', 0), 2);
        $total = round($monto + $adminFee, 2);

        $pago = Pago::query()->create([
            'servicio_id' => $servicio->id,
            'solicitud_cita_id' => $data['solicitud_cita_id'] ?? null,
            'cliente_nombre' => $data['cliente_nombre'],
            'cliente_email' => $data['cliente_email'],
            'cliente_telefono' => $data['cliente_telefono'] ?? null,
            'monto' => $total,
            'moneda' => strtolower((string) config('services.stripe.currency', 'pen')),
            'metodo' => $data['metodo'],
            'estado' => Pago::ESTADO_PENDING_MANUAL,
            'referencia_manual' => $data['referencia_manual'] ?? null,
            'notas' => $data['notas'] ?? null,
        ]);

        $pago->sincronizarEstadoSolicitudCita();

        $redirectUrl = ($pago->solicitud_cita_id !== null)
            ? FrontendPagoUrl::exitoCitaManual((int) $pago->solicitud_cita_id, $pago->id)
            : FrontendPagoUrl::registradoManual($pago->id);

        return response()->json([
            'ok' => true,
            'pago_id' => $pago->id,
            'solicitud_cita_id' => $pago->solicitud_cita_id,
            'redirect_url' => $redirectUrl,
            'message' => 'Solicitud registrada. La clínica confirmará tu pago al validar el comprobante.',
        ], 201);
    }

    public function verificar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session_id' => ['required', 'string', 'max:255'],
        ]);

        $pago = Pago::query()
            ->with('servicio:id,nombre')
            ->where('stripe_checkout_session_id', $data['session_id'])
            ->first();

        if ($pago === null) {
            return response()->json([
                'message' => 'No se encontró el pago para esta sesión.',
            ], 404);
        }

        if ($pago->estado !== Pago::ESTADO_PAID) {
            $this->sincronizarDesdeStripe($pago, $data['session_id']);
            $pago->refresh();
        }

        return response()->json([
            'pago' => $this->pagoPayload($pago),
        ]);
    }

    public function show(Pago $pago): JsonResponse
    {
        $pago->load('servicio:id,nombre');

        return response()->json([
            'pago' => $this->pagoPayload($pago),
        ]);
    }

    private function sincronizarDesdeStripe(Pago $pago, string $sessionId): void
    {
        $secret = config('services.stripe.secret');
        if (! is_string($secret) || $secret === '') {
            return;
        }

        try {
            Stripe::setApiKey($secret);
            $session = StripeCheckoutSession::retrieve($sessionId);
        } catch (ApiErrorException) {
            return;
        }

        if (($session->payment_status ?? '') === 'paid') {
            $pago->marcarPagado();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function pagoPayload(Pago $pago): array
    {
        return [
            'id' => $pago->id,
            'estado' => $pago->estado,
            'metodo' => $pago->metodo,
            'monto' => $pago->monto,
            'moneda' => $pago->moneda,
            'solicitud_cita_id' => $pago->solicitud_cita_id,
            'cliente_nombre' => $pago->cliente_nombre,
            'cliente_email' => $pago->cliente_email,
            'referencia_manual' => $pago->referencia_manual,
            'servicio' => $pago->servicio ? ['nombre' => $pago->servicio->nombre] : null,
            'paid_at' => $pago->paid_at?->toIso8601String(),
        ];
    }

    private static function toStripeAmount(float $soles, string $currency): int
    {
        $zeroDecimal = ['bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf'];

        if (in_array($currency, $zeroDecimal, true)) {
            return (int) round($soles);
        }

        return (int) round($soles * 100);
    }
}
