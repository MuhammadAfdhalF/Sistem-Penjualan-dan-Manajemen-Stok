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
use App\Models\User;

class ProsesTransaksiController extends Controller
{

    public function formBelanjaCepat(Request $request)
    {
        $user = Auth::user();
        $produkData = $request->input('produk_data', []);

        if (empty($produkData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 produk.');
        }

        // 🔥 SIMPAN data ke session agar tidak hilang
        session(['form_cepat_data' => $produkData]);

        // Ubah data untuk ditampilkan di view
        $produkCollection = collect($produkData)->map(function ($item) {
            $produk = Produk::with('hargaProduks', 'satuans')->find($item['produk_id']);
            if (!$produk) return null;

            return (object)[
                'produk' => $produk,
                'jumlah_json' => $item['jumlah_json'],
            ];
        })->filter();

        return view('mobile.proses_transaksi', [
            'jenis' => $user->jenis_pelanggan ?? 'Individu',
            'keranjangs' => $produkCollection,
            'activeMenu' => 'formcepat',
            'from_form_cepat' => true, // Penanda penting untuk view
        ]);
    }

    public function formBelanjaCepatStore(Request $request)
    {
        $user = Auth::user();

        // 🔥 AMBIL data dari session, bukan dari request form
        $produkData = session('form_cepat_data', []);

        if (empty($produkData)) {
            return redirect()->route('mobile.form_belanja_cepat.index')->with('error', 'Sesi belanja Anda telah berakhir. Silakan ulangi.');
        }

        $request->validate([
            'metode_pengambilan' => 'required|in:ambil di toko,diantar',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'alamat_pengambilan' => 'required_if:metode_pengambilan,diantar|nullable|string',
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

            foreach ($produkData as $item) {
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
                        return redirect()->back()->withInput()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }

                    $produk->decrement('stok', $jumlahUtama);

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

            DB::commit();

            // 🔥 Hapus session setelah transaksi berhasil
            session()->forget('form_cepat_data');
            Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    public function keranjang(Request $request)
    {
        $user = Auth::user();
        $keranjangIds = $request->input('keranjang_id', []);

        if (empty($keranjangIds)) {
            return redirect()->route('mobile.keranjang.index')->with('error', 'Anda harus memilih item di keranjang terlebih dahulu.');
        }

        session(['keranjang_ids' => $keranjangIds]);

        $keranjangs = Keranjang::with('produk.hargaProduks', 'produk.satuans')
            ->where('user_id', $user->id)
            ->whereIn('id', $keranjangIds)
            ->get();

        return view('mobile.proses_transaksi', [
            'jenis' => $user->jenis_pelanggan ?? 'Individu',
            'keranjangs' => $keranjangs,
            'activeMenu' => 'keranjang',
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $keranjangIds = session('keranjang_ids', []);

        if (empty($keranjangIds)) {
            return redirect()->route('mobile.keranjang.index')->with('error', 'Sesi keranjang Anda telah berakhir. Silakan ulangi.');
        }

        $request->validate([
            'metode_pengambilan' => 'required|in:ambil di toko,diantar',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'alamat_pengambilan' => 'required_if:metode_pengambilan,diantar|nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $keranjangs = $user->keranjangs()->whereIn('id', $keranjangIds)->get();

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
                        return redirect()->back()->withInput()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }

                    $produk->decrement('stok', $jumlahUtama);

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
            $user->keranjangs()->whereIn('id', $keranjangIds)->delete();
            session()->forget('keranjang_ids');

            DB::commit();
            Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }
}
