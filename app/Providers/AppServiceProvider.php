<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Pastikan baris ini ada

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\ProductInterface::class,
            \App\Services\ProductService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tambahkan logika ini untuk memaksa URL menjadi HTTPS jika APP_URL adalah HTTPS
        if (str_starts_with(env('APP_URL'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
