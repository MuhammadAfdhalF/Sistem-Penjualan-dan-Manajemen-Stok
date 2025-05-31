<?php

namespace App\Http\Controllers;

use App\Models\TransaksiOffline;
use App\Models\TransaksiOfflineDetail;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Keuangan;



class TransaksiOfflineController extends Controller
{

    public function index(Request $request)
    {
        $pelanggans = \App\Models\User::where('role', 'pelanggan')->orderBy('nama')->get();

        $query = \App\Models\TransaksiOffline::with(['detail.produk', 'pelanggan'])->latest();

        // Filter tanggal, bulan, tahun
        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->date);
        }
        if ($request->filled('month')) {
            $query->whereMonth('tanggal', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('tanggal', $request->year);
        }

        // Filter by pelanggan id
        if ($request->filled('pelanggan_id')) {
            $query->where('pelanggan_id', $request->pelanggan_id);
        }

        $transaksi = $query->get();

        return view('transaksi_offline.index', compact('transaksi', 'pelanggans'));
    }

    public function show($id)
    {
        $transaksi = \App\Models\TransaksiOffline::with([
            'pelanggan',
            'detail.produk',  // wajib untuk menampilkan nama produk di view
        ])->findOrFail($id);

        return view('transaksi_offline.show', compact('transaksi'));
    }


    public function create()
    {
        $produk = Produk::with('satuans')->get();  // <- eager load relasi satuan
        $pelanggans = User::where('role', 'pelanggan')->get();
        $kode_transaksi = 'TX-' . now()->format('ymd-His');
        $tanggal = now();

        return view('transaksi_offline.create', compact('produk', 'pelanggans', 'kode_transaksi', 'tanggal'));
    }

    public function store(Request $request)
    {
        \Log::info('produk_id:', $request->produk_id ?? []);
        \Log::info('jumlah_json:', $request->jumlah_json ?? []);
        \Log::info('harga_json:', $request->harga_json ?? []);

        $request->validate([
            'kode_transaksi' => 'required|unique:transaksi_offline,kode_transaksi',
            'tanggal' => 'required|date',
            'jenis_pelanggan' => 'required|in:Individu,Toko Kecil',
            'total' => 'required|numeric',
            'dibayar' => 'required|numeric',
            'kembalian' => 'required|numeric',
            'pelanggan_id' => 'nullable|exists:users,id',
            'produk_id.*' => 'required|exists:produks,id',
            'jumlah_json.*' => 'required|string',
            'harga_json.*' => 'required|string',
        ]);

        $sanitizeMoney = fn($val) => floatval(str_replace(['.', ','], ['', '.'], $val));

        try {
            DB::beginTransaction();

            $transaksi = TransaksiOffline::create([
                'kode_transaksi' => $request->kode_transaksi,
                'tanggal' => $request->tanggal,
                'jenis_pelanggan' => $request->jenis_pelanggan,
                'total' => $sanitizeMoney($request->total),
                'dibayar' => $sanitizeMoney($request->dibayar),
                'kembalian' => $sanitizeMoney($request->kembalian),
                'pelanggan_id' => $request->pelanggan_id ?? null,
            ]);

            foreach ($request->produk_id as $i => $produkId) {
                \Log::info("Processing produk index $i, produk_id: $produkId");

                $rawJumlahJson = $request->jumlah_json[$i] ?? null;
                $rawHargaJson = $request->harga_json[$i] ?? null;

                \Log::info("Raw jumlah_json[$i]: " . json_encode($rawJumlahJson));
                \Log::info("Raw harga_json[$i]: " . json_encode($rawHargaJson));

                // Pastikan raw JSON berupa string sebelum decode
                if (!is_string($rawJumlahJson) || !is_string($rawHargaJson)) {
                    \Log::error("Invalid JSON string for produk_id $produkId, skipping...");
                    continue;
                }

                $jumlahArr = json_decode($rawJumlahJson, true);
                $hargaArr = json_decode($rawHargaJson, true);

                \Log::info("Decoded jumlahArr:", (array) $jumlahArr);
                \Log::info("Decoded hargaArr:", (array) $hargaArr);

                if (
                    json_last_error() !== JSON_ERROR_NONE ||
                    empty($jumlahArr) || !is_array($jumlahArr) ||
                    empty($hargaArr) || !is_array($hargaArr)
                ) {
                    \Log::error("JSON decode error or invalid array for produk_id $produkId, skipping...");
                    continue;
                }

                $produk = Produk::findOrFail($produkId);

                $subtotalTotal = 0;
                $totalJumlahUtama = 0;

                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    if (!$satuan) {
                        \Log::warning("Satuan ID $satuanId tidak ditemukan, produk_id $produkId");
                        continue;
                    }

                    $hargaSatuanRaw = $hargaArr[$satuanId] ?? 0;
                    $hargaSatuan = $sanitizeMoney($hargaSatuanRaw);

                    $subtotal = $qty * $hargaSatuan;
                    $subtotalTotal += $subtotal;

                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $totalJumlahUtama += $qty * $konversi;
                }

                TransaksiOfflineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'jumlah_json' => $jumlahArr,
                    'harga_json' => $hargaArr,
                    'subtotal' => $subtotalTotal,
                ]);

                if ($produk->stok < $totalJumlahUtama) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                }

                $produk->stok -= $totalJumlahUtama;
                $produk->save();

                Stok::create([
                    'produk_id' => $produkId,
                    'jenis' => 'keluar',
                    'jumlah' => $totalJumlahUtama,
                    'keterangan' => 'Transaksi penjualan ' . $transaksi->kode_transaksi,
                ]);
            }

            Artisan::call('produk:update-dailyusage-rop');

            Keuangan::create([
                'transaksi_id' => $transaksi->id,
                'tanggal' => $request->tanggal,
                'jenis' => 'pemasukan',
                'nominal' => $transaksi->total,
                'keterangan' => 'Pemasukan dari transaksi #' . $transaksi->kode_transaksi,
                'sumber' => 'offline',
            ]);

            DB::commit();

            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving transaksi offline: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }


    public function edit($id)
    {
        $transaksi = TransaksiOffline::with('detail.produk', 'detail.produk.satuans')->findOrFail($id);
        $produk = Produk::with('satuans')->get();
        $pelanggans = User::where('role', 'pelanggan')->get();
        return view('transaksi_offline.edit', compact('transaksi', 'produk', 'pelanggans'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'total' => 'required|numeric',
            'dibayar' => 'required|numeric',
            'kembalian' => 'required|numeric',
            'pelanggan_id' => 'nullable|exists:users,id',
            'produk_id.*' => 'required|exists:produks,id',
            'jumlah_json.*' => 'required|string',
            'harga_json.*' => 'required|string',
        ]);

        $sanitizeMoney = fn($val) => floatval(str_replace(['.', ','], ['', '.'], $val));

        \DB::beginTransaction();
        try {
            $transaksi = TransaksiOffline::with('detail')->findOrFail($id);

            // Rollback stok lama dari detail lama
            foreach ($transaksi->detail as $detail) {
                $jumlahArr = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);
                if (!$jumlahArr) $jumlahArr = [];
                $totalJumlahUtama = 0;
                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    if (!$satuan) continue;
                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $totalJumlahUtama += $qty * $konversi;
                }
                $produk = \App\Models\Produk::findOrFail($detail->produk_id);
                $produk->stok += $totalJumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id' => $detail->produk_id,
                    'jenis' => 'masuk',
                    'jumlah' => $totalJumlahUtama,
                    'keterangan' => 'Rollback stok transaksi ' . $transaksi->kode_transaksi,
                ]);
            }

            // Hapus detail lama
            $transaksi->detail()->delete();

            // Update data transaksi utama
            $transaksi->update([
                'tanggal' => $request->tanggal,
                'total' => $sanitizeMoney($request->total),
                'dibayar' => $sanitizeMoney($request->dibayar),
                'kembalian' => $sanitizeMoney($request->kembalian),
                'pelanggan_id' => $request->pelanggan_id ?? null,
            ]);

            // Simpan detail baru
            foreach ($request->produk_id as $i => $produkId) {
                $jumlahArr = json_decode($request->jumlah_json[$i], true);
                $hargaArr = json_decode($request->harga_json[$i], true);

                if (
                    json_last_error() !== JSON_ERROR_NONE ||
                    empty($jumlahArr) || !is_array($jumlahArr) ||
                    empty($hargaArr) || !is_array($hargaArr)
                ) {
                    continue; // skip jika data tidak valid
                }

                $subtotalTotal = 0;
                $totalJumlahUtama = 0;

                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    if (!$satuan) continue;

                    $hargaSatuanRaw = $hargaArr[$satuanId] ?? 0;
                    $hargaSatuan = $sanitizeMoney($hargaSatuanRaw);
                    $subtotal = $qty * $hargaSatuan;
                    $subtotalTotal += $subtotal;

                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $totalJumlahUtama += $qty * $konversi;
                }

                TransaksiOfflineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'jumlah_json' => $jumlahArr,
                    'harga_json' => $hargaArr,
                    'subtotal' => $subtotalTotal,
                ]);

                $produk = \App\Models\Produk::findOrFail($produkId);

                if ($produk->stok < $totalJumlahUtama) {
                    \DB::rollBack();
                    return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                }

                $produk->stok -= $totalJumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id' => $produkId,
                    'jenis' => 'keluar',
                    'jumlah' => $totalJumlahUtama,
                    'keterangan' => 'Update stok transaksi ' . $transaksi->kode_transaksi,
                ]);
            }

            // Update keuangan
            $keuangan = \App\Models\Keuangan::where('transaksi_id', $transaksi->id)->first();
            if ($keuangan) {
                $keuangan->update([
                    'tanggal' => $request->tanggal,
                    'nominal' => $transaksi->total,
                    'keterangan' => 'Pemasukan dari transaksi #' . $transaksi->kode_transaksi,
                    'sumber' => 'offline',
                ]);
            }

            \Artisan::call('produk:update-dailyusage-rop');
            \DB::commit();

            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }



    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $transaksi = \App\Models\TransaksiOffline::with('detail')->findOrFail($id);

            foreach ($transaksi->detail as $detail) {
                $jumlahArr = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);
                if (!$jumlahArr) $jumlahArr = [];

                $totalJumlahUtama = 0;
                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    if (!$satuan) continue;

                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $totalJumlahUtama += $qty * $konversi;
                }

                $produk = $detail->produk;
                if ($produk) {
                    $produk->stok += $totalJumlahUtama;
                    $produk->save();

                    \App\Models\Stok::create([
                        'produk_id' => $detail->produk_id,
                        'jenis' => 'masuk',
                        'jumlah' => $totalJumlahUtama,
                        'keterangan' => 'Transaksi dihapus (' . $transaksi->kode_transaksi . ')',
                    ]);
                }
            }

            // Hapus detail transaksi
            $transaksi->detail()->delete();

            // Hapus catatan keuangan terkait (jika ada)
            \App\Models\Keuangan::where('transaksi_id', $transaksi->id)->delete();

            // Hapus transaksi
            $transaksi->delete();

            Artisan::call('produk:update-dailyusage-rop');
            DB::commit();

            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
