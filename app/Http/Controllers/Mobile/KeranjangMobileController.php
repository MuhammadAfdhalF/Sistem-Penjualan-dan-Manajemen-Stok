<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Log awal request (optional)
        \Log::info('[KERANJANG MOBILE] store input', [
            'produk_ids'    => $produk_ids,
            'jumlah_jsons'  => $jumlah_jsons,
            'request_all'   => $request->all(),
        ]);

        DB::beginTransaction();
        try {
            foreach ($produk_ids as $i => $produk_id) {
                $jumlah_json = $jumlah_jsons[$i] ?? null;
                \Log::info('[KERANJANG MOBILE] Loop produk', [
                    'index'             => $i,
                    'produk_id'         => $produk_id,
                    'jumlah_json_raw'   => $jumlah_json,
                ]);
                if (!$produk_id || !$jumlah_json) continue;

                // Pastikan $jumlah_json selalu array associative satuan_id => qty
                $daftarJumlah = is_array($jumlah_json)
                    ? $jumlah_json
                    : json_decode($jumlah_json, true);

                // Safety jika decode gagal
                if (!is_array($daftarJumlah)) $daftarJumlah = [];
                \Log::info('[KERANJANG MOBILE] jumlah_json parsed', [
                    'produk_id'   => $produk_id,
                    'daftarJumlah' => $daftarJumlah,
                ]);

                if (empty($daftarJumlah)) continue;

                // --- CEK DAN KURANGI STOK ----
                foreach ($daftarJumlah as $satuan_id => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;

                    $satuan = \App\Models\Satuan::find($satuan_id);
                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    $jumlahUtama = $qty * $konversi;

                    $produk = \App\Models\Produk::find($produk_id);
                    if ($produk) {
                        if ($produk->stok < $jumlahUtama) {
                            DB::rollBack();
                            $msg = "Stok tidak cukup untuk produk {$produk->nama_produk}.";
                            if ($request->ajax()) {
                                return response()->json(['success' => false, 'message' => $msg], 400);
                            }
                            return redirect()->back()->with('error', $msg);
                        }
                        $produk->stok -= $jumlahUtama;
                        $produk->save();

                        // Catat log stok keluar (optional)
                        \App\Models\Stok::create([
                            'produk_id' => $produk_id,
                            'satuan_id' => $satuan_id,
                            'jenis' => 'keluar',
                            'jumlah' => $jumlahUtama,
                            'keterangan' => 'Masuk keranjang (mobile): ' . ($user->nama ?? 'User #' . $user->id),
                        ]);
                    }
                }

                // Gabungkan jumlah per satuan jika sudah ada di keranjang
                $keranjang = \App\Models\Keranjang::where('user_id', $user->id)
                    ->where('produk_id', $produk_id)
                    ->first();

                if ($keranjang) {
                    $existing = $keranjang->jumlah_json;
                    if (is_string($existing)) $existing = json_decode($existing, true);
                    if (!is_array($existing)) $existing = [];

                    foreach ($daftarJumlah as $satuan_id => $qty) {
                        $qty = floatval($qty);
                        if (isset($existing[$satuan_id])) {
                            $existing[$satuan_id] += $qty;
                        } else {
                            $existing[$satuan_id] = $qty;
                        }
                    }
                    $keranjang->jumlah_json = $existing;
                    $keranjang->save();
                    \Log::info('[KERANJANG MOBILE] Keranjang updated', [
                        'produk_id' => $produk_id,
                        'jumlah_json_final' => $keranjang->jumlah_json,
                    ]);
                } else {
                    $baru = \App\Models\Keranjang::create([
                        'user_id' => $user->id,
                        'produk_id' => $produk_id,
                        'jumlah_json' => $daftarJumlah,
                    ]);
                    \Log::info('[KERANJANG MOBILE] Keranjang created', [
                        'produk_id' => $produk_id,
                        'jumlah_json_final' => $baru->jumlah_json,
                    ]);
                }
            }
            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Produk dimasukkan ke keranjang anda']);
            }
            return redirect()->route('mobile.keranjang.index')->with('success', 'Item berhasil ditambahkan ke keranjang.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('[KERANJANG MOBILE] ERROR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan item ke keranjang: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal menambahkan item ke keranjang: ' . $e->getMessage());
        }
    }

    // UPDATE JUMLAH ITEM DI KERANJANG
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $keranjang = Keranjang::where('user_id', $user->id)->findOrFail($id);

        $oldJumlah = $keranjang->jumlah_json;
        if (is_string($oldJumlah)) $oldJumlah = json_decode($oldJumlah, true);
        if (!is_array($oldJumlah)) $oldJumlah = [];

        DB::beginTransaction();
        try {
            // 1. Kembalikan stok dari jumlah lama
            foreach ($oldJumlah as $satuan_id => $qty) {
                $qty = floatval($qty);
                if ($qty <= 0) continue;

                $satuan = \App\Models\Satuan::find($satuan_id);
                $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                $jumlahUtama = $qty * $konversi;

                $produk = Produk::find($keranjang->produk_id);
                if ($produk) {
                    $produk->stok += $jumlahUtama;
                    $produk->save();

                    \App\Models\Stok::create([
                        'produk_id'  => $produk->id,
                        'satuan_id'  => $satuan_id,
                        'jenis'      => 'masuk',
                        'jumlah'     => $jumlahUtama,
                        'keterangan' => 'Restok sebelum update keranjang (mobile): ' . ($user->nama ?? 'User #' . $user->id),
                    ]);
                }
            }

            // 2. Ambil input baru
            $input = $request->input('jumlah_json');
            if (is_string($input)) {
                $jumlahBaru = json_decode($input, true);
            } else {
                $jumlahBaru = $input;
            }

            if (!is_array($jumlahBaru)) {
                DB::rollBack();
                $msg = 'Format jumlah tidak valid.';
                return $request->ajax()
                    ? response()->json(['success' => false, 'message' => $msg], 400)
                    : redirect()->back()->with('error', $msg);
            }

            // 3. Filter dan normalisasi input
            $jumlahBaru = collect($jumlahBaru)
                ->filter(fn($qty) => is_numeric($qty) && $qty > 0)
                ->map(fn($qty) => floatval($qty))
                ->toArray();

            if (empty($jumlahBaru)) {
                DB::rollBack();
                $msg = 'Jumlah tidak boleh kosong.';
                return $request->ajax()
                    ? response()->json(['success' => false, 'message' => $msg], 400)
                    : redirect()->back()->with('error', $msg);
            }

            // 4. Cek stok baru dan kurangi
            $produk = Produk::find($keranjang->produk_id);
            if (!$produk) {
                DB::rollBack();
                $msg = 'Produk tidak ditemukan.';
                return $request->ajax()
                    ? response()->json(['success' => false, 'message' => $msg], 404)
                    : redirect()->back()->with('error', $msg);
            }

            $totalPengurangan = 0;
            foreach ($jumlahBaru as $satuan_id => $qty) {
                $satuan = \App\Models\Satuan::find($satuan_id);
                $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                $jumlahUtama = $qty * $konversi;
                $totalPengurangan += $jumlahUtama;
            }

            if ($produk->stok < $totalPengurangan) {
                DB::rollBack();
                $msg = "Stok tidak cukup untuk produk {$produk->nama_produk}.";
                return $request->ajax()
                    ? response()->json(['success' => false, 'message' => $msg], 400)
                    : redirect()->back()->with('error', $msg);
            }

            // 5. Kurangi stok dan simpan log
            foreach ($jumlahBaru as $satuan_id => $qty) {
                $satuan = \App\Models\Satuan::find($satuan_id);
                $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                $jumlahUtama = $qty * $konversi;

                $produk->stok -= $jumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id'  => $produk->id,
                    'satuan_id'  => $satuan_id,
                    'jenis'      => 'keluar',
                    'jumlah'     => $jumlahUtama,
                    'keterangan' => 'Update keranjang (mobile): ' . ($user->nama ?? 'User #' . $user->id),
                ]);
            }

            // 6. Simpan ke keranjang
            $keranjang->jumlah_json = $jumlahBaru;
            $keranjang->save();

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Jumlah berhasil diupdate']);
            }

            return redirect()->route('mobile.keranjang.index')->with('success', 'Jumlah item di keranjang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('[KERANJANG UPDATE] ERROR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $msg = 'Gagal update keranjang: ' . $e->getMessage();
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $msg], 500)
                : redirect()->back()->with('error', $msg);
        }
    }


    // HAPUS ITEM DI KERANJANG
    public function destroy($id)
    {
        $user = Auth::user();
        $item = Keranjang::where('user_id', $user->id)->findOrFail($id);

        $jumlahJson = $item->jumlah_json;
        if (is_string($jumlahJson)) $jumlahJson = json_decode($jumlahJson, true);
        if (!is_array($jumlahJson)) $jumlahJson = [];

        $produk = Produk::find($item->produk_id);

        foreach ($jumlahJson as $satuan_id => $qty) {
            $qty = floatval($qty);
            if ($qty <= 0) continue;
            $satuan = \App\Models\Satuan::find($satuan_id);
            $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
            $jumlahUtama = $qty * $konversi;
            if ($produk) {
                $produk->stok += $jumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id'  => $item->produk_id,
                    'satuan_id'  => $satuan_id,
                    'jenis'      => 'masuk',
                    'jumlah'     => $jumlahUtama,
                    'keterangan' => 'Hapus keranjang (mobile): ' . ($user->nama ?? 'User #' . $user->id) . ' - Stok Dikembalikan',
                ]);
            }
        }

        $item->delete();

        return redirect()->route('mobile.keranjang.index')->with('success', 'Item berhasil dihapus dari keranjang dan stok dikembalikan.');
    }
}
