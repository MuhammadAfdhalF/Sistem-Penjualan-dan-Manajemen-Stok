<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent; // Pastikan ini ada jika Anda menggunakan Agent
use Illuminate\Validation\ValidationException; // Tambahkan ini
use Illuminate\Support\Facades\Session; // BARIS INI DITAMBAHKAN!
use App\Models\User; // Tambahkan ini juga, agar model User bisa dikenali

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
        Session::forget('url.intended');
        if ($user->role === 'admin') {
            return redirect('/dashboard');
        }
        if ($user->role === 'pelanggan') {
            return redirect('/pelanggan-area/home');
        }
        return redirect('/pelanggan-area/home');
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * Ini adalah metode yang perlu Anda tambahkan/modifikasi.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Ambil input email dari request
        $email = $request->input($this->username()); // 'email' by default

        // Cek apakah email terdaftar di database
        $user = \App\Models\User::where($this->username(), $email)->first(); // Asumsi model User Anda ada di App\Models\User

        if (!$user) {
            // Jika email tidak ditemukan
            throw ValidationException::withMessages([
                $this->username() => ['Email tidak terdaftar.'],
            ])->redirectTo(route('login')); // Redirect kembali ke halaman login
        } else {
            // Jika email ditemukan, tapi password salah
            throw ValidationException::withMessages([
                'password' => ['Password yang Anda masukkan salah.'],
            ])->redirectTo(route('login')); // Redirect kembali ke halaman login
        }
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
