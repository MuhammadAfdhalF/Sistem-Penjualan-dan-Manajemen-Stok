<?php

return [
    'serverKey' => env('MIDTRANS_SERVER_KEY'),
    // Pastikan env('MIDTRANS_IS_PRODUCTION') dikonversi ke boolean
    'isProduction' => filter_var(env('MIDTRANS_IS_PRODUCTION'), FILTER_VALIDATE_BOOLEAN),
    'isSanitized' => filter_var(env('MIDTRANS_IS_SANITIZED', true), FILTER_VALIDATE_BOOLEAN),
    'is3ds' => filter_var(env('MIDTRANS_IS_3DS', true), FILTER_VALIDATE_BOOLEAN),
];
