<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;


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

    // public function showLoginForm(Request $request)
    // {
    //     $agent = new \Jenssegers\Agent\Agent();

    //     if ($request->has('force')) {
    //         if ($request->force === 'mobile') {
    //             return view('auth.login_mobile');
    //         } elseif ($request->force === 'desktop') {
    //             return view('auth.login');
    //         }
    //     }

    //     return $agent->isDesktop()
    //         ? view('auth.login')
    //         : view('auth.login_mobile');
    // }
}
