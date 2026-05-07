<?php

/**
 * Credenciales solo para pruebas del panel /admin (sin usuario en base de datos).
 * Cambia estos valores o usa DEV_LOGIN_* en .env antes de exponer el proyecto.
 */
return [
    'email' => env('DEV_LOGIN_EMAIL', 'admin@local.test'),

    'password' => env('DEV_LOGIN_PASSWORD', 'password'),

    'name' => env('DEV_LOGIN_NAME', 'Administrador'),
];
