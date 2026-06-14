<?php

namespace App\Support;

/**
 * URLs del sitio React (HashRouter) para retorno de Stripe Checkout.
 */
final class FrontendPagoUrl
{
    public static function hashRoute(string $route): string
    {
        $base = rtrim(FrontendPublicUrl::resolve(), '/');

        return $base.'#/'.ltrim($route, '/');
    }

    public static function exitoStripe(): string
    {
        return self::hashRoute('pago/exito?session_id={CHECKOUT_SESSION_ID}');
    }

    public static function canceladoStripe(): string
    {
        return self::hashRoute('pago/cancelado');
    }

    public static function registradoManual(int $pagoId): string
    {
        return self::hashRoute('pago/registrado?pago_id='.$pagoId);
    }
}
