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



class TransaksiOfflineController extends Controller
{

    public function index()
    {
        $transaksi = TransaksiOffline::with('detail.produk')->latest()->get();
        return view('transaksi_offline.index', compact('transaksi'));
    }

    public function show($id)
    {
        $transaksi = TransaksiOffline::with('detail.produk')->findOrFail($id);
        return view('transaksi_offline.show', compact('transaksi'));
    }


    public function create()
    {
        $produk = Produk::all();
        $kode_transaksi = 'TRX-' . strtoupper(Str::random(6));
        $tanggal = Carbon::now(); // tidak pakai ->format()

        return view('transaksi_offline.create', compact('produk', 'kode_transaksi', 'tanggal'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'kode_transaksi' => 'required|unique:transaksi_offline,kode_transaksi',
            'tanggal' => 'required|date',
            'jenis_pelanggan' => 'required|in:Individu,Toko Kecil',
            'total' => 'required',
            'dibayar' => 'required',
            'kembalian' => 'required',
            'produk_id.*' => 'required|exists:produks,id',
            'satuan_id.*' => 'required|exists:satuans,id',
            'jumlah.*' => 'required|numeric|min:0.01',
            'harga.*' => 'required|numeric|min:0',
        ]);

        $sanitizeMoney = fn($val) => floatval(str_replace(['.', ','], ['', '.'], $val));

        try {
            \DB::beginTransaction();

            // Simpan transaksi utama
            $transaksi = \App\Models\TransaksiOffline::create([
                'kode_transaksi' => $request->kode_transaksi,
                'tanggal' => $request->tanggal,
                'jenis_pelanggan' => $request->jenis_pelanggan,
                'total' => $sanitizeMoney($request->total),
                'dibayar' => $sanitizeMoney($request->dibayar),
                'kembalian' => $sanitizeMoney($request->kembalian),
            ]);

            foreach ($request->produk_id as $i => $produkId) {
                $satuanId = $request->satuan_id[$i];
                $jumlah = (float) $request->jumlah[$i];
                $harga = $sanitizeMoney($request->harga[$i]);

                // Cari harga_id dari harga_produks
                $hargaModel = \App\Models\HargaProduk::where('produk_id', $produkId)
                    ->where('satuan_id', $satuanId)
                    ->where('jenis_pelanggan', $request->jenis_pelanggan)
                    ->first();

                if (!$hargaModel) {
                    \DB::rollBack();
                    return redirect()->back()->with('error', "Harga belum tersedia untuk produk, satuan, dan jenis pelanggan ini.");
                }

                // Simpan detail transaksi
                \App\Models\TransaksiOfflineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'satuan_id' => $satuanId,
                    'harga_id' => $hargaModel->id,
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $harga * $jumlah,
                ]);

                // Hitung jumlah satuan utama dan kurangi stok
                $satuan = \App\Models\Satuan::findOrFail($satuanId);
                $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                $jumlahDalamSatuanUtama = $jumlah * $konversi;

                $produk = \App\Models\Produk::findOrFail($produkId);
                if ($produk->stok < $jumlahDalamSatuanUtama) {
                    \DB::rollBack();
                    return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                }

                $produk->stok -= $jumlahDalamSatuanUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id' => $produkId,
                    'jenis' => 'keluar',
                    'jumlah' => $jumlahDalamSatuanUtama,
                    'keterangan' => 'Transaksi penjualan ' . $transaksi->kode_transaksi,
                ]);
            }

            // Panggil command update ROP
            \Artisan::call('produk:update-dailyusage-rop');

            // Simpan keuangan otomatis
            \App\Models\Keuangan::create([
                'transaksi_id' => $transaksi->id,
                'tanggal' => $request->tanggal,
                'jenis' => 'pemasukan',
                'nominal' => $transaksi->total,
                'keterangan' => 'Pemasukan dari transaksi #' . $transaksi->kode_transaksi,
                'sumber' => 'offline',
            ]);

            \DB::commit();

            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }





    public function edit($id)
    {
        $transaksi = TransaksiOffline::with('detail')->findOrFail($id);
        $produk = Produk::all();
        return view('transaksi_offline.edit', compact('transaksi', 'produk'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal'       => 'required|date',
            'total'         => 'required',
            'dibayar'       => 'required',
            'kembalian'     => 'required',
            'produk_id.*'   => 'required|exists:produks,id',
            'satuan_id.*'   => 'required|exists:satuans,id',
            'jumlah.*'      => 'required|numeric|min:0.01',
            'harga.*'       => 'required|numeric|min:0',
        ]);

        $sanitizeMoney = fn($val) => floatval(str_replace(['.', ','], ['', '.'], $val));

        \DB::beginTransaction();
        try {
            $transaksi = \App\Models\TransaksiOffline::with('detail')->findOrFail($id);

            // Rollback stok lama
            foreach ($transaksi->detail as $detail) {
                $satuan = \App\Models\Satuan::findOrFail($detail->satuan_id);
                $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                $jumlahUtama = $detail->jumlah * $konversi;

                $produk = \App\Models\Produk::findOrFail($detail->produk_id);
                $produk->stok += $jumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id' => $detail->produk_id,
                    'jenis' => 'masuk',
                    'jumlah' => $jumlahUtama,
                    'keterangan' => 'Rollback transaksi ' . $transaksi->kode_transaksi,
                ]);
            }

            // Hapus detail lama
            $transaksi->detail()->delete();

            // Update transaksi utama
            $transaksi->update([
                'tanggal'   => $request->tanggal,
                'total'     => $sanitizeMoney($request->total),
                'dibayar'   => $sanitizeMoney($request->dibayar),
                'kembalian' => $sanitizeMoney($request->kembalian),
            ]);

            // Simpan ulang detail
            foreach ($request->produk_id as $i => $produkId) {
                $satuanId = $request->satuan_id[$i];
                $jumlah = (float) $request->jumlah[$i];
                $harga = $sanitizeMoney($request->harga[$i]);

                $hargaModel = \App\Models\HargaProduk::where('produk_id', $produkId)
                    ->where('satuan_id', $satuanId)
                    ->where('jenis_pelanggan', $transaksi->jenis_pelanggan)
                    ->first();

                if (!$hargaModel) {
                    \DB::rollBack();
                    return redirect()->back()->with('error', "Harga tidak ditemukan untuk kombinasi produk, satuan, dan jenis pelanggan.");
                }

                \App\Models\TransaksiOfflineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id'    => $produkId,
                    'satuan_id'    => $satuanId,
                    'harga_id'     => $hargaModel->id,
                    'jumlah'       => $jumlah,
                    'harga'        => $harga,
                    'subtotal'     => $harga * $jumlah,
                ]);

                $produk = \App\Models\Produk::findOrFail($produkId);
                $satuan = \App\Models\Satuan::findOrFail($satuanId);
                $jumlahUtama = $jumlah * $satuan->konversi_ke_satuan_utama;

                if ($produk->stok < $jumlahUtama) {
                    \DB::rollBack();
                    return redirect()->back()->with('error', 'Stok tidak mencukupi untuk produk: ' . $produk->nama_produk);
                }

                $produk->stok -= $jumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id' => $produkId,
                    'jenis'     => 'keluar',
                    'jumlah'    => $jumlahUtama,
                    'keterangan' => 'Update transaksi ' . $transaksi->kode_transaksi,
                ]);
            }

            // Update data keuangan terkait transaksi ini
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
                $produk = $detail->produk;
                if ($produk) {
                    $satuan = \App\Models\Satuan::findOrFail($detail->satuan_id);
                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $jumlahUtama = $detail->jumlah * $konversi;

                    $produk->stok += $jumlahUtama;
                    $produk->save();

                    \App\Models\Stok::create([
                        'produk_id' => $detail->produk_id,
                        'jenis' => 'masuk',
                        'jumlah' => $jumlahUtama,
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
