<?php

$originsRaw = env('CORS_ALLOWED_ORIGINS', '*');
$origins = is_string($originsRaw) && trim($originsRaw) !== ''
    ? array_values(array_filter(array_map('trim', explode(',', $originsRaw))))
    : ['*'];

$supportsCredentials = filter_var(env('CORS_SUPPORTS_CREDENTIALS', false), FILTER_VALIDATE_BOOL);

if ($supportsCredentials && in_array('*', $origins, true)) {
    $origins = array_values(array_filter(array_unique(array_filter([
        env('FRONTEND_URL'),
        env('FRONTEND_URL_FALLBACK'),
    ]))));
}

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'auth/*',
        'login',
        'logout',
        'register',
        'forgot-password',
        'reset-password',
        'email/*',
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => $origins !== [] ? $origins : ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => $supportsCredentials,
];
