<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeranjangMobileController extends Controller
{
    public function keranjang(Request $request)
    {
        $user = Auth::user();
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';
        // Untuk sekarang cukup return view (data bisa ditambah nanti)
        return view('mobile.keranjang');
    }
}
