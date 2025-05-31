<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     * @var string
     */
    // protected $redirectTo = '/produk'; // kita override dengan method redirectTo()

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Override redirect path after login based on user role.
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return '/dashboard';   
        }

        if ($user->role === 'pelanggan') {
            return '/home';      
        }

        // default fallback jika role tidak dikenali
        return '/home';
    }
}
