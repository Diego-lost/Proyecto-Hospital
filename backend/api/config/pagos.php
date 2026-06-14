<?php

return [

  'yape_phone' => env('PAGOS_YAPE_PHONE', '(01) 123-4567'),

  'pagos_email' => env('PAGOS_EMAIL', 'pagos@novasalud.pe'),

  'bank' => [
    'nombre' => env('PAGOS_BANCO_NOMBRE', 'Banco de Crédito del Perú (BCP)'),
    'cuenta' => env('PAGOS_BANCO_CUENTA', '191-12345678-0-12'),
    'titular' => env('PAGOS_BANCO_TITULAR', 'Clínica NovaSalud S.A.C.'),
    'cci' => env('PAGOS_BANCO_CCI', '002-191-001234567812-12'),
  ],

  'admin_fee' => (float) env('PAGOS_ADMIN_FEE', 0),

];
