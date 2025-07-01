<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Tambahkan URL webhook Midtrans Anda di sini
        'midtrans/webhook', // Ini adalah path relatif dari APP_URL Anda
        // Jika Anda memiliki versi Laravel yang lebih lama dan menggunakan route helper
        // Anda juga bisa mencoba:
        // route('mobile.midtrans.webhook', [], false), // false untuk URL relatif
    ];
}
