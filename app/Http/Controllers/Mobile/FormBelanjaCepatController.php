<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Produk;

class FormBelanjaCepatController extends Controller
{
    public function index(Request $request)
    {
        // Ambil user login & jenis pelanggan
        $user = Auth::user();
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

        // Ambil semua kategori produk unik
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

        // Ambil produk beserta satuan dan harga sesuai jenis pelanggan
        $produk = $query->with(['satuans', 'hargaProduks' => function ($q) use ($jenisPelanggan) {
            $q->where('jenis_pelanggan', $jenisPelanggan);
        }])->get();

        return view('mobile.form_belanja_cepat', [
            'produk' => $produk,
            'listKategori' => $listKategori,
            'filterKategori' => $filterKategori,
            'searchQuery' => $searchQuery,
            'activeMenu' => 'formcepat'
        ]);
    }
}
