<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proveedor tipo OpenAI (HTTP)
    |--------------------------------------------------------------------------
    |
    | Compatible con OpenAI oficial y proveedores con la misma ruta
    | POST {base_url}/chat/completions (p. ej. Azure AI con URL completa en base_url).
    |
    */

    'api_key' => env('AI_API_KEY'),

    'triage_enabled' => filter_var(env('AI_TRIAGE_ENABLED', true), FILTER_VALIDATE_BOOL),

    /*
    |--------------------------------------------------------------------------
    | Proveedor de IA para triaje
    |--------------------------------------------------------------------------
    |
    | openai: usa /chat/completions compatible OpenAI.
    | dxgpt: usa endpoint diagnose en API Management (DxGPT).
    |
    */
    'provider' => env('AI_PROVIDER', 'openai'),

    'base_url' => rtrim((string) env('AI_BASE_URL', 'https://api.openai.com/v1'), '/'),

    'model' => env('AI_MODEL', 'gpt-4o-mini'),

    /*
    |--------------------------------------------------------------------------
    | DxGPT
    |--------------------------------------------------------------------------
    */
    'dxgpt_base_url' => rtrim((string) env('DXGPT_BASE_URL', ''), '/'),
    'dxgpt_diagnose_path' => env('DXGPT_DIAGNOSE_PATH', '/diagnose'),
    'dxgpt_subscription_key' => env('DXGPT_SUBSCRIPTION_KEY'),
    'dxgpt_model' => env('DXGPT_MODEL', 'gpt4o'),
    'dxgpt_timezone' => env('DXGPT_TIMEZONE', 'America/Lima'),

    'timeout' => (int) env('AI_TIMEOUT', 45),

    'max_sugerencias' => (int) env('AI_MAX_SUGERENCIAS', 3),

    /*
    | Si el proveedor no soporta response_format=json_object (p. ej. algunos proxies),
    | ponga AI_JSON_OBJECT_RESPONSE=false.
    */
    'json_object_response' => filter_var(env('AI_JSON_OBJECT_RESPONSE', true), FILTER_VALIDATE_BOOL),

    'disclaimer_es' => 'Orientación informativa para elegir especialidad. No sustituye valoración médica ni constituye diagnóstico.',
];
