<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Force redirect user after login based on role,
     * overriding Laravel's intended behavior.
     */
    protected function authenticated(Request $request, $user)
    {
        \Session::forget('url.intended');
        if ($user->role === 'admin') {
            return redirect('/dashboard');
        }
        if ($user->role === 'pelanggan') {
            return redirect('/pelanggan-area/home');
        }
        return redirect('/pelanggan-area/home');
    }
}
