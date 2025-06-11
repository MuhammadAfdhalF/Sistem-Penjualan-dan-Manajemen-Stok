<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Produk;
use App\Models\Satuan;
use App\Models\HargaProduk;
use App\Models\TransaksiOnline;
use App\Models\TransaksiOnlineDetail;
use App\Models\Keuangan;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use App\Models\User; // Jika perlu digunakan eksplisit


class ProsesTransaksiController extends Controller
{
    public function keranjang(Request $request)
    {
        $user = Auth::user();
        $jenis = $user->jenis_pelanggan ?? 'Individu';

        $keranjangs = Keranjang::with(['produk.satuans', 'produk.hargaProduks'])
            ->where('user_id', $user->id)
            ->get();

        return view('mobile.proses_transaksi', [
            'jenis' => $jenis,
            'keranjangs' => $keranjangs,
            'activeMenu' => 'keranjang'
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'metode_pengambilan' => 'required|in:ambil di toko,diantar',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'alamat_pengambilan' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $keranjangs = $user->keranjangs()->with('produk.hargaProduks', 'produk.satuans')->get();
        if ($keranjangs->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang kosong.');
        }

        DB::beginTransaction();
        try {
            $total = 0;
            $kode = 'TX-ON-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
            $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

            $transaksi = TransaksiOnline::create([
                'user_id' => $user->id,
                'kode_transaksi' => $kode,
                'tanggal' => now(),
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => 'pending',
                'status_transaksi' => 'diproses',
                'catatan' => $request->catatan,
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->alamat_pengambilan,
                'total' => 0,
            ]);

            foreach ($keranjangs as $item) {
                $produk = $item->produk;
                $jumlahArr = $item->jumlah_json;
                $subtotalProduk = 0;
                $hargaArr = [];

                foreach ($jumlahArr as $satuanId => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;

                    $satuan = Satuan::findOrFail($satuanId);
                    $harga = HargaProduk::where('produk_id', $produk->id)
                        ->where('satuan_id', $satuanId)
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->value('harga') ?? 0;

                    $hargaArr[$satuanId] = $harga;
                    $subtotalProduk += $harga * $qty;

                    // Kurangi stok
                    $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                    $jumlahUtama = $qty * $konversi;

                    if ($produk->stok < $jumlahUtama) {
                        DB::rollBack();
                        return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }

                    $produk->stok -= $jumlahUtama;
                    $produk->save();

                    // Catat log stok keluar
                    \App\Models\Stok::create([
                        'produk_id' => $produk->id,
                        'satuan_id' => $satuanId,
                        'jenis' => 'keluar',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Transaksi online #' . $kode,
                    ]);
                }

                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'jumlah_json' => $jumlahArr,
                    'harga_json' => $hargaArr,
                    'subtotal' => $subtotalProduk,
                ]);

                $total += $subtotalProduk;
            }

            $transaksi->update(['total' => $total]);

            // Catat keuangan jika lunas
            if ($request->metode_pembayaran === 'payment_gateway') {
                \App\Models\Keuangan::create([
                    'transaksi_online_id' => $transaksi->id,
                    'tanggal' => now(),
                    'jenis' => 'pemasukan',
                    'nominal' => $total,
                    'keterangan' => 'Pemasukan dari transaksi online #' . $kode,
                    'sumber' => 'online',
                ]);
            }

            // Kosongkan keranjang
            $user->keranjangs()->delete();

            Artisan::call('produk:update-dailyusage-rop');
            DB::commit();

            return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }
}
