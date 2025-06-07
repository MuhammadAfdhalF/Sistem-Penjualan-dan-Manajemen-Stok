<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';


        // Ambil semua kategori unik
        $listKategori = Produk::select('kategori')
            ->distinct()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->pluck('kategori');

        // Ambil parameter filter dari request
        $filterKategori = $request->kategori;
        $searchQuery = $request->search;

        // Query produk dengan filter kategori dan pencarian
        $query = Produk::query();

        if ($filterKategori) {
            $query->where('kategori', $filterKategori);
        }

        if ($searchQuery) {
            $query->where('nama_produk', 'like', "%{$searchQuery}%");
        }

        $produk = $query->with(['satuans', 'hargaProduks' => function ($q) use ($jenisPelanggan) {
            $q->where('jenis_pelanggan', $jenisPelanggan);
        }])->get();



        return view('mobile.home', compact('produk', 'listKategori', 'filterKategori', 'searchQuery'));
    }
}
