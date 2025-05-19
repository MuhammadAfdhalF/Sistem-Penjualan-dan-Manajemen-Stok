<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil semua produk
        $produk = Produk::all();

        // Pisahkan produk berdasarkan status stok vs ROP
        $produkMenipis = $produk->filter(function ($item) {
            return $item->stok <= $item->rop;
        });

        return view('dashboard.index', compact('produk', 'produkMenipis'));
    }
}
