<?php

namespace App\Http\Controllers;

use App\Models\TransaksiOnline;
use App\Models\TransaksiOnlineDetail;
use App\Models\Produk;
use App\Models\Satuan;
use App\Models\HargaProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;


class TransaksiOnlineController extends Controller
{
    public function index()
    {
        $transaksis = TransaksiOnline::with('user')->latest()->get();
        return view('transaksi_online.index', compact('transaksis'));
    }

    public function create()
    {
        $users = \App\Models\User::where('role', 'pelanggan')->get();

        $produks = \App\Models\Produk::with([
            'satuans',
            'hargaProduks' => function ($query) {
                $query->orderBy('satuan_id'); // optional, biar terstruktur
            }
        ])->get();

        return view('transaksi_online.create', compact('users', 'produks'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'status_pembayaran' => 'required|in:pending,lunas,gagal',
            'status_transaksi' => 'required|in:diproses,diantar,diambil,selesai,batal',
            'produk_id.*' => 'required|exists:produks,id',
            'satuan_id.*' => 'required|exists:satuans,id',
            'jumlah.*' => 'required|numeric|min:0.01',
            'harga.*' => 'required|numeric|min:0',
        ]);

        $sanitize = fn($val) => floatval(str_replace(['.', ','], ['', '.'], $val));

        DB::beginTransaction();
        try {
            $total = 0;
            $kode = 'TRX-ONLINE-' . strtoupper(Str::random(6));

            $transaksi = TransaksiOnline::create([
                'user_id' => $request->user_id,
                'kode_transaksi' => $kode,
                'tanggal' => $request->tanggal,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $request->status_pembayaran,
                'status_transaksi' => $request->status_transaksi,
                'catatan' => $request->catatan,
                'diambil_di_toko' => $request->diambil_di_toko ?? false,
                'alamat_pengambilan' => $request->alamat_pengambilan,
                'total' => 0,
            ]);

            foreach ($request->produk_id as $i => $produkId) {
                $satuanId = $request->satuan_id[$i];
                $jumlah = $sanitize($request->jumlah[$i]);
                $harga = $sanitize($request->harga[$i]);
                $subtotal = $jumlah * $harga;

                $hargaModel = HargaProduk::where('produk_id', $produkId)
                    ->where('satuan_id', $satuanId)
                    ->first();

                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'satuan_id' => $satuanId,
                    'harga_id' => $hargaModel->id ?? null,
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ]);

                // Update stok
                $satuan = \App\Models\Satuan::findOrFail($satuanId);
                $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                $jumlahUtama = $jumlah * $konversi;

                $produk = \App\Models\Produk::findOrFail($produkId);
                if ($produk->stok < $jumlahUtama) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                }

                $produk->stok -= $jumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id' => $produkId,
                    'satuan_id' => $satuanId,
                    'jenis' => 'keluar',
                    'jumlah' => $jumlahUtama,
                    'keterangan' => 'Transaksi online #' . $kode,
                ]);

                $total += $subtotal;
            }

            $transaksi->update(['total' => $total]);

            // Simpan keuangan hanya jika status pembayaran lunas
            if ($request->status_pembayaran === 'lunas') {
                \App\Models\Keuangan::create([
                    'transaksi_online_id' => $transaksi->id,
                    'tanggal' => $request->tanggal,
                    'jenis' => 'pemasukan',
                    'nominal' => $total,
                    'keterangan' => 'Pemasukan dari transaksi online #' . $kode,
                    'sumber' => 'online',
                ]);
            }

            Artisan::call('produk:update-dailyusage-rop');
            DB::commit();

            return redirect()->route('transaksi_online.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }




    public function show(TransaksiOnline $transaksiOnline)
    {
        $transaksiOnline->load(['detail.produk', 'detail.satuan']);
        return view('transaksi_online.show', compact('transaksiOnline'));
    }

    public function edit(TransaksiOnline $transaksiOnline)
    {
        $users = \App\Models\User::where('role', 'pelanggan')->get();

        $produks = \App\Models\Produk::with([
            'satuans',
            'hargaProduks' => function ($query) {
                $query->orderBy('satuan_id');
            }
        ])->get();

        $transaksiOnline->load(['detail']);

        return view('transaksi_online.edit', compact('transaksiOnline', 'users', 'produks'));
    }

    public function update(Request $request, TransaksiOnline $transaksiOnline)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'status_pembayaran' => 'required|in:pending,lunas,gagal',
            'status_transaksi' => 'required|in:diproses,diantar,diambil,selesai,batal',
            'produk_id.*' => 'required|exists:produks,id',
            'satuan_id.*' => 'required|exists:satuans,id',
            'jumlah.*' => 'required|numeric|min:0.01',
            'harga.*' => 'required|numeric|min:0',
        ]);

        $sanitize = fn($val) => floatval(str_replace(['.', ','], ['', '.'], $val));

        DB::beginTransaction();
        try {
            // Rollback stok lama
            foreach ($transaksiOnline->detail as $detail) {
                $produk = $detail->produk;
                $satuan = \App\Models\Satuan::find($detail->satuan_id);
                $konversi = $satuan?->konversi_ke_satuan_utama ?? 1;
                $jumlahUtama = $detail->jumlah * $konversi;

                if ($produk) {
                    $produk->stok += $jumlahUtama;
                    $produk->save();

                    \App\Models\Stok::create([
                        'produk_id' => $produk->id,
                        'satuan_id' => $satuan?->id,
                        'jenis' => 'masuk',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Rollback transaksi online #' . $transaksiOnline->kode_transaksi,
                    ]);
                }
            }

            // Update transaksi utama
            $transaksiOnline->update([
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $request->status_pembayaran,
                'status_transaksi' => $request->status_transaksi,
                'catatan' => $request->catatan,
                'diambil_di_toko' => $request->diambil_di_toko ?? false,
                'alamat_pengambilan' => $request->alamat_pengambilan,
            ]);

            // Hapus detail lama
            $transaksiOnline->detail()->delete();

            $total = 0;

            foreach ($request->produk_id as $i => $produkId) {
                $satuanId = $request->satuan_id[$i];
                $jumlah = $sanitize($request->jumlah[$i]);
                $harga = $sanitize($request->harga[$i]);
                $subtotal = $jumlah * $harga;

                $hargaModel = HargaProduk::where('produk_id', $produkId)
                    ->where('satuan_id', $satuanId)
                    ->first();

                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksiOnline->id,
                    'produk_id' => $produkId,
                    'satuan_id' => $satuanId,
                    'harga_id' => $hargaModel->id ?? null,
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ]);

                // Update stok baru
                $satuan = \App\Models\Satuan::findOrFail($satuanId);
                $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                $jumlahUtama = $jumlah * $konversi;

                $produk = \App\Models\Produk::findOrFail($produkId);
                if ($produk->stok < $jumlahUtama) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                }

                $produk->stok -= $jumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id' => $produkId,
                    'satuan_id' => $satuanId,
                    'jenis' => 'keluar',
                    'jumlah' => $jumlahUtama,
                    'keterangan' => 'Update transaksi online #' . $transaksiOnline->kode_transaksi,
                ]);

                $total += $subtotal;
            }

            $transaksiOnline->update(['total' => $total]);

            // Kelola data keuangan
            $keuangan = \App\Models\Keuangan::where('transaksi_online_id', $transaksiOnline->id)->first();

            if ($request->status_pembayaran === 'lunas') {
                if ($keuangan) {
                    $keuangan->update([
                        'tanggal' => $request->tanggal,
                        'nominal' => $total,
                        'keterangan' => 'Pemasukan dari transaksi online #' . $transaksiOnline->kode_transaksi,
                        'sumber' => 'online',
                    ]);
                } else {
                    \App\Models\Keuangan::create([
                        'transaksi_online_id' => $transaksiOnline->id,
                        'tanggal' => $request->tanggal,
                        'jenis' => 'pemasukan',
                        'nominal' => $total,
                        'keterangan' => 'Pemasukan dari transaksi online #' . $transaksiOnline->kode_transaksi,
                        'sumber' => 'online',
                    ]);
                }
            } else {
                if ($keuangan) {
                    $keuangan->delete();
                }
            }

            Artisan::call('produk:update-dailyusage-rop');
            DB::commit();

            return redirect()->route('transaksi_online.index')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }



    public function destroy(TransaksiOnline $transaksiOnline)
    {
        DB::beginTransaction();
        try {
            // Rollback stok dari semua detail
            foreach ($transaksiOnline->detail as $detail) {
                $produk = $detail->produk;
                $satuan = \App\Models\Satuan::find($detail->satuan_id);
                $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                $jumlahUtama = $detail->jumlah * $konversi;

                if ($produk) {
                    $produk->stok += $jumlahUtama;
                    $produk->save();

                    \App\Models\Stok::create([
                        'produk_id' => $detail->produk_id,
                        'satuan_id' => $detail->satuan_id,
                        'jenis' => 'masuk',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Transaksi online dihapus (#' . $transaksiOnline->kode_transaksi . ')',
                    ]);
                }
            }

            // Hapus detail transaksi
            $transaksiOnline->detail()->delete();

            // Hapus catatan keuangan jika ada
            \App\Models\Keuangan::where('keterangan', 'like', '%#' . $transaksiOnline->kode_transaksi)->delete();

            // Hapus transaksi
            $transaksiOnline->delete();

            Artisan::call('produk:update-dailyusage-rop');
            DB::commit();

            return redirect()->route('transaksi_online.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
