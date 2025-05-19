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
            'total' => 'required',
            'dibayar' => 'required',
            'kembalian' => 'required',
            'produk_id.*' => 'required|exists:produks,id',
            'jumlah.*' => 'required|integer|min:1',
            'harga.*' => 'required',
        ]);

        // Fungsi bantu untuk membersihkan format uang
        $sanitizeMoney = function ($value) {
            return floatval(str_replace(['.', ','], ['', '.'], $value));
        };

        try {
            DB::beginTransaction();

            // Buat transaksi
            $transaksi = TransaksiOffline::create([
                'kode_transaksi' => $request->kode_transaksi,
                'tanggal' => $request->tanggal,
                'total' => $sanitizeMoney($request->total),
                'dibayar' => $sanitizeMoney($request->dibayar),
                'kembalian' => $sanitizeMoney($request->kembalian),
            ]);

            foreach ($request->produk_id as $i => $produkId) {
                $harga = $sanitizeMoney($request->harga[$i]);
                $jumlah = $request->jumlah[$i];

                TransaksiOfflineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $harga * $jumlah,
                ]);

                $produk = Produk::find($produkId);
                if ($produk && $produk->stok >= $jumlah) {
                    $produk->stok -= $jumlah;
                    $produk->save();

                    Stok::create([
                        'produk_id' => $produkId,
                        'jenis' => 'keluar',
                        'jumlah' => $jumlah,
                        'keterangan' => 'Transaksi penjualan ' . $transaksi->kode_transaksi,
                    ]);
                } else {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Stok produk tidak cukup.');
                }
            }

            DB::commit();

            // Panggil command update daily_usage dan rop setelah transaksi berhasil
            Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil disimpan dan ROP diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
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
            'tanggal' => 'required|date',
            'total' => 'required',
            'dibayar' => 'required',
            'kembalian' => 'required',
            'produk_id.*' => 'required|exists:produks,id',
            'jumlah.*' => 'required|integer|min:1',
            'harga.*' => 'required',
        ]);

        // Fungsi bantu untuk membersihkan format uang
        $sanitizeMoney = function ($value) {
            return floatval(str_replace(['.', ','], ['', '.'], $value));
        };

        try {
            $transaksi = TransaksiOffline::findOrFail($id);

            // Kembalikan stok lama (rollback stok)
            $oldDetails = TransaksiOfflineDetail::where('transaksi_id', $transaksi->id)->get();
            foreach ($oldDetails as $detail) {
                $produk = Produk::find($detail->produk_id);
                if ($produk) {
                    $produk->stok += $detail->jumlah;
                    $produk->save();
                }
            }

            // Update transaksi utama
            $transaksi->update([
                'tanggal' => $request->tanggal,
                'total' => $sanitizeMoney($request->total),
                'dibayar' => $sanitizeMoney($request->dibayar),
                'kembalian' => $sanitizeMoney($request->kembalian),
            ]);

            // Hapus detail transaksi lama
            TransaksiOfflineDetail::where('transaksi_id', $transaksi->id)->delete();

            // Tambahkan ulang detail + update stok
            foreach ($request->produk_id as $i => $produkId) {
                $jumlah = $request->jumlah[$i];
                $harga = $sanitizeMoney($request->harga[$i]);

                $produk = Produk::find($produkId);
                if ($produk) {
                    $produk->stok -= $jumlah;
                    $produk->save();
                }

                TransaksiOfflineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => $harga * $jumlah,
                ]);
            }

            DB::commit();

            // Panggil command update daily_usage dan rop setelah transaksi berhasil
            Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $transaksi = TransaksiOffline::findOrFail($id);
            $details = TransaksiOfflineDetail::where('transaksi_id', $id)->get();

            // Mengembalikan stok produk yang digunakan dalam transaksi
            foreach ($details as $detail) {
                $produk = Produk::find($detail->produk_id);

                // Mengurangi stok produk di tabel produk
                $produk->stok += $detail->jumlah;
                $produk->save();

                // Menyimpan perubahan stok di tabel stok
                \App\Models\Stok::create([
                    'produk_id' => $detail->produk_id,
                    'jenis' => 'masuk', // Barang kembali masuk ke stok
                    'jumlah' => $detail->jumlah,
                    'keterangan' => 'Transaksi dihapus, stok dikembalikan',
                ]);
            }

            // Hapus detail transaksi
            TransaksiOfflineDetail::where('transaksi_id', $id)->delete();

            // Hapus transaksi
            $transaksi->delete();
            DB::commit();

            // Panggil command update daily_usage dan rop setelah transaksi berhasil
            Artisan::call('produk:update-dailyusage-rop');
            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil dihapus dan stok dikembalikan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
