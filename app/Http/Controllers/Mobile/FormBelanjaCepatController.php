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

    public function validateCheckout(Request $request)
    {
        $user = Auth::user();
        $productsToCheckout = $request->input('products_to_checkout', []);

        if (empty($productsToCheckout)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada produk yang dipilih atau jumlahnya 0.'], 400);
        }

        $errors = [];

        foreach ($productsToCheckout as $productData) {
            $produkId = $productData['produk_id'];
            $requestedJumlahJson = $productData['jumlah_json'];

            $produk = Produk::with('satuans')->find($produkId);
            if (!$produk) {
                $errors[] = "Produk dengan ID {$produkId} tidak ditemukan.";
                continue;
            }

            $totalJumlahDimintaUtama = 0;
            foreach ($requestedJumlahJson as $satuanId => $qty) {
                $qty = floatval($qty);
                if ($qty <= 0) continue;

                $satuan = $produk->satuans->firstWhere('id', $satuanId);
                if (!$satuan) {
                    $errors[] = "Satuan tidak valid untuk produk '{$produk->nama_produk}'.";
                    continue 2; // Lompati ke produk berikutnya jika satuan tidak valid
                }
                $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                $totalJumlahDimintaUtama += $qty * $konversi;
            }

            // Pastikan ada jumlah valid yang diminta untuk produk ini
            if ($totalJumlahDimintaUtama <= 0) {
                // Jika produk dipilih tapi total qty 0, lewati dan jangan dianggap error stok
                // karena bisa jadi user belum mengisi kuantitas
                continue;
            }


            // Lakukan pengecekan stok dengan total jumlah yang diminta pengguna di frontend
            if ($produk->stok < $totalJumlahDimintaUtama) {
                // Asumsi method tampilkanStok3Tingkatan ada di Produk model
                $stokTersediaFormatted = $produk->tampilkanStok3Tingkatan($produk->stok);
                $errors[] = "Stok tidak cukup untuk produk '{$produk->nama_produk}'. Stok tersedia: {$stokTersediaFormatted}.";
            }
        }

        if (!empty($errors)) {
            // Mengembalikan revert_jumlah_json yang berisi current (tersedia) stok untuk setiap produk bermasalah
            $revertData = [];
            foreach ($productsToCheckout as $productData) {
                $produkId = $productData['produk_id'];
                $produk = Produk::with('satuans')->find($produkId);
                if ($produk) {
                    // Hitung jumlah maksimal yang tersedia dalam setiap satuan untuk produk ini
                    $maxAvailableQuantities = [];
                    $satuansSorted = $produk->satuans->sortByDesc('konversi_ke_satuan_utama');
                    $remainingStokUtama = $produk->stok;

                    foreach ($satuansSorted as $satuan) {
                        $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                        if ($konversi > 0) {
                            $qtyInThisUnit = floor($remainingStokUtama / $konversi);
                            if ($qtyInThisUnit > 0) {
                                $maxAvailableQuantities[$satuan->id] = $qtyInThisUnit;
                                $remainingStokUtama -= ($qtyInThisUnit * $konversi);
                            } else {
                                $maxAvailableQuantities[$satuan->id] = 0;
                            }
                        } else {
                            $maxAvailableQuantities[$satuan->id] = 0;
                        }
                    }
                    $revertData[$produkId] = $maxAvailableQuantities;
                }
            }

            return response()->json([
                'success' => false,
                'message' => implode('<br>', $errors),
                'revert_data' => $revertData // Mengirim data stok yang tersedia per produk
            ], 400);
        }

        return response()->json(['success' => true, 'message' => 'Stok mencukupi, lanjutkan ke konfirmasi.']);
    }
}
