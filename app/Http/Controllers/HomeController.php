<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        if ($user) {
            if ($user->role === 'admin') {
                return redirect('/dashboard');
            }
            if ($user->role === 'pelanggan') {
                return redirect('/pelanggan-area/home');
            }
        }
        return redirect('/')->with('error', 'Akses tidak diizinkan.');
    }
}
