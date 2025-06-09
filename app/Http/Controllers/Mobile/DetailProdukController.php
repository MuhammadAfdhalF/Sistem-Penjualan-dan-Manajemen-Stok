<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetailProdukController extends Controller
{
    public function index($id, Request $request)
    {
        // Ambil user login & jenis pelanggan
        $user = Auth::user();
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

        // Produk + satuans + hargaProduk untuk jenis pelanggan ini saja
        $produk = Produk::with([
            'satuans',
            'hargaProduks' => function ($q) use ($jenisPelanggan) {
                $q->where('jenis_pelanggan', $jenisPelanggan);
            }
        ])->findOrFail($id);

        return view('mobile.detail_produk', compact('produk', 'jenisPelanggan') + [
            'activeMenu' => 'home'
        ]);
    }
}
