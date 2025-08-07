<?php

if (!function_exists('asset')) {
    function asset($path, $secure = null)
    {
        // Tambahkan 'public/' supaya asset URL valid di Hostinger
        return app('url')->asset('public/' . ltrim($path, '/'), $secure);
    }
}
