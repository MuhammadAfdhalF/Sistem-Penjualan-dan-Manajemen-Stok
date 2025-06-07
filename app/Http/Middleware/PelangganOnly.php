<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PelangganOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'pelanggan') {
            return $next($request);
        }
        return redirect('/home')->with('error', 'Akses ditolak. Hanya pelanggan yang dapat mengakses halaman ini.');
    }
}
