<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProsesTransaksiController extends Controller
{
    // TAMPILKAN KERANJANG PELANGGAN (index)
    public function keranjang(Request $request)
    {
        $user = Auth::user();
        $jenis = $user->jenis_pelanggan ?? 'Individu';



        return view('mobile.proses_transaksi', [

            'jenis' => $jenis,
            'activeMenu' => 'keranjang'
        ]);
    }
}
