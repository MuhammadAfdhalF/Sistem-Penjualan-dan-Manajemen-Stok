<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;  // BARIS INI PENTING UNTUK MENGATASI ERROR 'Log'




class KeranjangMobileController extends Controller
{
    // TAMPILKAN KERANJANG PELANGGAN (index)
    public function keranjang(Request $request)
    {
        $user = Auth::user();
        $jenis = $user->jenis_pelanggan ?? 'Individu';

        $keranjangs = Keranjang::with(['produk.satuans', 'produk.hargaProduks'])
            ->where('user_id', $user->id)
            ->get();

        return view('mobile.keranjang', [
            'keranjangs' => $keranjangs,
            'jenis' => $jenis,
            'activeMenu' => 'keranjang'
        ]);
    }

    // FORM TAMBAH KE KERANJANG
    public function create()
    {
        $user = Auth::user();
        $jenis = $user->jenis_pelanggan ?? 'Individu';
        $produks = Produk::with([
            'satuans',
            'hargaProduks' => function ($q) use ($jenis) {
                $q->where('jenis_pelanggan', $jenis);
            }
        ])->get();

        return view('mobile.keranjang_create', compact('produks', 'jenis'));
    }

    // SIMPAN ITEM KE KERANJANG
    public function store(Request $request)
    {
        $user = Auth::user();

        $produk_ids = $request->input('produk_id', []);
        $jumlah_jsons = $request->input('jumlah_json', []);

        // Log input awal untuk debugging
        Log::info('[KERANJANG MOBILE] store input', [
            'produk_ids'    => $produk_ids,
            'jumlah_jsons'  => $jumlah_jsons,
            'request_all'   => $request->all(),
        ]);

        DB::beginTransaction(); // Memulai transaksi database
        try {
            foreach ($produk_ids as $i => $produk_id) {
                $jumlah_json = $jumlah_jsons[$i] ?? null;
                Log::info('[KERANJANG MOBILE] Loop produk', [
                    'index'             => $i,
                    'produk_id'         => $produk_id,
                    'jumlah_json_raw'   => $jumlah_json,
                ]);

                // Lewati jika produk_id atau jumlah_json kosong
                if (!$produk_id || !$jumlah_json) {
                    continue;
                }

                // Pastikan $jumlah_json adalah array asosiatif satuan_id => qty
                $daftarJumlah = is_array($jumlah_json)
                    ? $jumlah_json
                    : json_decode($jumlah_json, true);

                // Safety jika decode gagal atau bukan array
                if (!is_array($daftarJumlah)) {
                    $daftarJumlah = [];
                }

                Log::info('[KERANJANG MOBILE] jumlah_json parsed', [
                    'produk_id'     => $produk_id,
                    'daftarJumlah'  => $daftarJumlah,
                ]);

                // Lewati jika daftar jumlah kosong setelah parsing
                if (empty($daftarJumlah)) {
                    continue;
                }

                // --- START: LOGIKA PENGECEKAN STOK ---
                $produk = Produk::with('satuans')->find($produk_id);
                if (!$produk) {
                    DB::rollBack();
                    $msg = 'Produk tidak ditemukan.';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg], 404);
                    }
                    return redirect()->back()->with('error', $msg);
                }

                $totalJumlahDimintaUtama = 0;
                foreach ($daftarJumlah as $satuan_id => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue; // Abaikan jumlah nol atau negatif

                    $satuan = $produk->satuans->firstWhere('id', $satuan_id);
                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    $totalJumlahDimintaUtama += $qty * $konversi;
                }

                // Lakukan pengecekan stok di sini
                if ($produk->stok < $totalJumlahDimintaUtama) {
                    DB::rollBack();
                    $stokTersediaFormatted = $produk->tampilkanStok3Tingkatan($produk->stok);
                    $msg = "Stok tidak cukup untuk produk '{$produk->nama_produk}'. Stok tersedia: {$stokTersediaFormatted}.";

                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => $msg], 400);
                    }
                    return redirect()->back()->with('error', $msg);
                }
                // --- END: LOGIKA PENGECEKAN STOK ---


                // Gabungkan jumlah per satuan jika sudah ada di keranjang
                $keranjang = Keranjang::where('user_id', $user->id)
                    ->where('produk_id', $produk_id)
                    ->first();

                if ($keranjang) {
                    // Jika item sudah ada, gabungkan jumlah per satuan
                    $existing = $keranjang->jumlah_json;
                    // Pastikan $existing adalah array (karena model cast ke array)
                    if (!is_array($existing)) {
                        $existing = [];
                    }

                    foreach ($daftarJumlah as $satuan_id => $qty) {
                        $qty = floatval($qty); // Pastikan qty adalah float
                        if (isset($existing[$satuan_id])) {
                            $existing[$satuan_id] += $qty;
                        } else {
                            $existing[$satuan_id] = $qty;
                        }
                    }
                    $keranjang->jumlah_json = $existing; // Update kolom jumlah_json
                    $keranjang->save(); // Simpan perubahan ke database
                    Log::info('[KERANJANG MOBILE] Keranjang updated', [
                        'produk_id' => $produk_id,
                        'jumlah_json_final' => $keranjang->jumlah_json,
                    ]);
                } else {
                    // Jika item belum ada, buat entri keranjang baru
                    $baru = Keranjang::create([
                        'user_id' => $user->id,
                        'produk_id' => $produk_id,
                        'jumlah_json' => $daftarJumlah, // Simpan daftar jumlah per satuan
                    ]);
                    Log::info('[KERANJANG MOBILE] Keranjang created', [
                        'produk_id' => $produk_id,
                        'jumlah_json_final' => $baru->jumlah_json,
                    ]);
                }
            }
            DB::commit(); // Komit transaksi jika semua operasi berhasil

            // Respons berdasarkan jenis permintaan (AJAX atau HTTP biasa)
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Produk dimasukkan ke keranjang anda']);
            }
            return redirect()->route('mobile.keranjang.index')->with('success', 'Item berhasil ditambahkan ke keranjang.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi kesalahan
            Log::error('[KERANJANG MOBILE] ERROR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            // Respons error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan item ke keranjang: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal menambahkan item ke keranjang: ' . $e->getMessage());
        }
    }

    // UPDATE JUMLAH ITEM DI KERANJANG   */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Cari item keranjang berdasarkan ID dan user ID
        $keranjang = Keranjang::where('user_id', $user->id)->findOrFail($id);

        // Ambil input jumlah baru dari request
        $inputJumlahBaru = $request->input('jumlah_json');

        // Pastikan input di-decode jika berupa string JSON
        if (is_string($inputJumlahBaru)) {
            $jumlahBaru = json_decode($inputJumlahBaru, true);
        } else {
            $jumlahBaru = $inputJumlahBaru;
        }

        // Validasi format jumlah
        if (!is_array($jumlahBaru)) {
            $msg = 'Format jumlah tidak valid.';
            $revertJumlahJson = json_decode($keranjang->getRawOriginal('jumlah_json'), true) ?? [];
            return response()->json(['success' => false, 'message' => $msg, 'revert_jumlah_json' => $revertJumlahJson], 400);
        }

        // Filter dan normalisasi input: pastikan hanya angka positif
        $jumlahBaruFiltered = collect($jumlahBaru)
            ->filter(fn($qty) => is_numeric($qty) && $qty >= 0)
            ->map(fn($qty) => floatval($qty))
            ->toArray();

        // Jika array $jumlahBaruFiltered menjadi kosong setelah filter,
        // (misal, jika inputnya tidak ada angka sama sekali),
        // maka kita set menjadi array kosong secara eksplisit.
        if (empty($jumlahBaruFiltered)) {
            $jumlahBaruFiltered = [];
        }

        DB::beginTransaction();
        try {
            $produk = Produk::with('satuans')->find($keranjang->produk_id);
            if (!$produk) {
                DB::rollBack();
                $msg = 'Produk tidak ditemukan.';
                $revertJumlahJson = json_decode($keranjang->getRawOriginal('jumlah_json'), true) ?? [];
                return response()->json(['success' => false, 'message' => $msg, 'revert_jumlah_json' => $revertJumlahJson], 404);
            }

            $totalNewJumlahUtama = 0;
            foreach ($jumlahBaruFiltered as $satuan_id => $qty) {
                $satuan = $produk->satuans->firstWhere('id', $satuan_id);
                if (!$satuan) {
                    DB::rollBack();
                    $msg = 'Satuan tidak valid untuk produk ini.';
                    $revertJumlahJson = json_decode($keranjang->getRawOriginal('jumlah_json'), true) ?? [];
                    return response()->json(['success' => false, 'message' => $msg, 'revert_jumlah_json' => $revertJumlahJson], 400);
                }
                $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                $totalNewJumlahUtama += $qty * $konversi;
            }

            // Lakukan pengecekan stok
            if ($totalNewJumlahUtama > $produk->stok) {
                DB::rollBack();
                $stokTersediaFormatted = $produk->tampilkanStok3Tingkatan($produk->stok);
                $msg = "Jumlah yang diminta untuk produk '{$produk->nama_produk}' melebihi stok tersedia. Stok: {$stokTersediaFormatted}.";

                // --- START PERUBAHAN DI SINI ---
                // Hitung jumlah maksimal yang tersedia dalam setiap satuan
                $maxAvailableQuantities = [];
                // Sortir satuan berdasarkan konversi ke satuan utama (dari besar ke kecil)
                // agar jika stok hanya 10 liter, dan ada satuan Box=12 liter, maka Box akan jadi 0
                // dan Liter akan menjadi 10.
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
                // --- END PERUBAHAN DI SINI ---

                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'revert_jumlah_json' => $maxAvailableQuantities, // KIRIMKAN INI!
                ], 400);
            }

            // Simpan jumlah baru ke keranjang
            $keranjang->jumlah_json = $jumlahBaruFiltered;
            $keranjang->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Jumlah berhasil diupdate']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[KERANJANG UPDATE] ERROR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $msg = 'Gagal update keranjang: ' . $e->getMessage();

            // Untuk error umum, kita masih bisa mengirim revert ke nilai lama di DB
            $revertJumlahJson = json_decode($keranjang->getRawOriginal('jumlah_json'), true) ?? [];
            return response()->json([
                'success' => false,
                'message' => $msg,
                'revert_jumlah_json' => $revertJumlahJson
            ], 500);
        }
    }


    // HAPUS ITEM DI KERANJANG
    public function destroy($id)
    {
        $user = Auth::user();
        // Cari dan hapus item keranjang
        $item = Keranjang::where('user_id', $user->id)->findOrFail($id);

        // --- BAGIAN INI DIHAPUS: TIDAK ADA PENGEMBALIAN STOK SAAT MENGHAPUS DARI KERANJANG ---
        // Logika pengembalian stok telah dihapus.
        // Karena stok tidak dikurangi saat masuk keranjang, tidak perlu dikembalikan saat dihapus.

        $item->delete(); // Hapus item dari keranjang

        return redirect()->route('mobile.keranjang.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }
}
