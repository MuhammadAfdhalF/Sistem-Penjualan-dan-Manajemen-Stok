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

    public function formBelanjaCepat(Request $request)
    {
        $user = Auth::user();
        $jenis = $user->jenis_pelanggan ?? 'Individu';

        $produkData = $request->input('produk_data', []); // array dari form cepat

        // Validasi minimum 1 produk
        if (empty($produkData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 produk.');
        }

        // Ambil data produk dan konversi jadi mirip struktur keranjang
        $produkCollection = collect($produkData)->map(function ($item) {
            return (object)[
                'produk' => Produk::with('hargaProduks', 'satuans')->find($item['produk_id']),
                'jumlah_json' => $item['jumlah_json'], // format: [satuan_id => jumlah]
            ];
        });

        return view('mobile.proses_transaksi', [
            'jenis' => $jenis,
            'keranjangs' => $produkCollection, // kita treat seolah ini “keranjang”
            'activeMenu' => 'formcepat',
            'from_form_cepat' => true, // biar bisa dibedakan nanti
        ]);
    }

    public function formBelanjaCepatStore(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'produk_data' => 'required|array|min:1',
            'produk_data.*.produk_id' => 'required|integer|exists:produks,id',
            'produk_data.*.jumlah_json' => 'required|array|min:1',
            'metode_pengambilan' => 'required|in:ambil di toko,diantar',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'alamat_pengambilan' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';
            $kode = 'TX-ON-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));

            $transaksi = TransaksiOnline::create([
                'user_id' => $user->id,
                'kode_transaksi' => $kode,
                'tanggal' => now(),
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => 'pending',
                'status_transaksi' => 'diproses',
                'catatan' => $request->catatan,
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
                'total' => 0,
            ]);

            foreach ($request->produk_data as $item) {
                $produk = Produk::with('satuans')->findOrFail($item['produk_id']);
                $jumlahArr = $item['jumlah_json'];
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

                    $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                    $jumlahUtama = $qty * $konversi;

                    if ($produk->stok < $jumlahUtama) {
                        DB::rollBack();
                        return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }

                    $produk->stok -= $jumlahUtama;
                    $produk->save();

                    Stok::create([
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

            if ($request->metode_pembayaran === 'payment_gateway') {
                Keuangan::create([
                    'transaksi_online_id' => $transaksi->id,
                    'tanggal' => now(),
                    'jenis' => 'pemasukan',
                    'nominal' => $total,
                    'keterangan' => 'Pemasukan dari transaksi online #' . $kode,
                    'sumber' => 'online',
                ]);
            }

            Artisan::call('produk:update-dailyusage-rop');
            DB::commit();

            return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }


    public function keranjang(Request $request)
    {
        $user = Auth::user();
        $jenis = $user->jenis_pelanggan ?? 'Individu';

        $keranjangIds = $request->input('keranjang_id', []);
        $keranjangs = Keranjang::with('produk.hargaProduks', 'produk.satuans')
            ->where('user_id', $user->id)
            ->whereIn('id', $keranjangIds)
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
            'keranjang_id' => 'required|array|min:1',
            'keranjang_id.*' => 'integer|exists:keranjangs,id',
        ]);

        $keranjangs = $user->keranjangs()
            ->with('produk.hargaProduks', 'produk.satuans')
            ->whereIn('id', $request->keranjang_id)
            ->get();

        if ($keranjangs->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada produk yang dipilih.');
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
                'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
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

                    $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                    $jumlahUtama = $qty * $konversi;

                    if ($produk->stok < $jumlahUtama) {
                        DB::rollBack();
                        return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }

                    $produk->stok -= $jumlahUtama;
                    $produk->save();

                    Stok::create([
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

            if ($request->metode_pembayaran === 'payment_gateway') {
                Keuangan::create([
                    'transaksi_online_id' => $transaksi->id,
                    'tanggal' => now(),
                    'jenis' => 'pemasukan',
                    'nominal' => $total,
                    'keterangan' => 'Pemasukan dari transaksi online #' . $kode,
                    'sumber' => 'online',
                ]);
            }

            $user->keranjangs()->whereIn('id', $request->keranjang_id)->delete();

            Artisan::call('produk:update-dailyusage-rop');
            DB::commit();

            return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }
}
