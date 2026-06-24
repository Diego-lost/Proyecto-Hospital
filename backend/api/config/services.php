<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Perú API — consulta DNI: https://peruapi.com/ (GET /api/dni/{dni}?api_token=…)
    */
    'peruapi' => [
        'key' => env('PERU_API_KEY'),
        'base_url' => env('PERU_API_BASE_URL', 'https://peruapi.com'),
        'timeout' => (int) env('PERU_API_TIMEOUT', 15),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => strtolower((string) env('STRIPE_CURRENCY', 'pen')),
    ],

    /*
    | Google Maps — Directions API (despacho de ambulancias en el panel admin).
    */
    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
        'directions_url' => env('GOOGLE_MAPS_DIRECTIONS_URL', 'https://maps.googleapis.com/maps/api/directions/json'),
        'origin_lat' => (float) env('CLINIC_ORIGIN_LAT', -12.0653),
        'origin_lng' => (float) env('CLINIC_ORIGIN_LNG', -75.2046),
        'origin_city' => env('CLINIC_ORIGIN_CITY', 'Huancayo'),
        'origin_address' => env('CLINIC_ORIGIN_ADDRESS', 'Av. Giráldez, Huancayo'),
        'timeout' => (int) env('GOOGLE_MAPS_TIMEOUT', 20),
        // En local permite despachar aunque Directions API falle (clave sin habilitar, etc.).
        'dispatch_without_route' => filter_var(
            env('GOOGLE_MAPS_DISPATCH_WITHOUT_ROUTE', env('APP_ENV', 'production') === 'local'),
            FILTER_VALIDATE_BOOL
        ),
        'osrm_fallback' => filter_var(env('GOOGLE_MAPS_OSRM_FALLBACK', true), FILTER_VALIDATE_BOOL),
        'osrm_url' => env('GOOGLE_MAPS_OSRM_URL', 'https://router.project-osrm.org'),
    ],

];
