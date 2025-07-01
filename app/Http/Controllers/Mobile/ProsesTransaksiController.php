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
use Illuminate\Support\Facades\Log;

class ProsesTransaksiController extends Controller
{

    public function formBelanjaCepat(Request $request)
    {
        Log::info('Metode formBelanjaCepat diakses.');
        $user = Auth::user();
        $produkData = $request->input('produk_data', []);

        if (empty($produkData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 produk.');
        }

        session(['form_cepat_data' => $produkData]);

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
            'from_form_cepat' => true,
        ]);
    }

    public function formBelanjaCepatStore(Request $request)
    {
        Log::info('Metode formBelanjaCepatStore berhasil diakses.');
        $user = Auth::user();
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

        DB::beginTransaction(); // Mulai transaksi database di sini
        try {
            $total = 0;
            $kode = 'TX-ON-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
            $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

            // Hitung total harga dari produkData
            foreach ($produkData as $item) {
                $produk = Produk::with('satuans')->findOrFail($item['produk_id']);
                $jumlahArr = $item['jumlah_json'];

                foreach ($jumlahArr as $satuanId => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;

                    $satuan = Satuan::findOrFail($satuanId);
                    $harga = HargaProduk::where('produk_id', $produk->id)
                        ->where('satuan_id', $satuanId)
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->value('harga') ?? 0;

                    $total += $harga * $qty;
                }
            }

            // 🔥 Buat entri TransaksiOnline di database SEBELUM memanggil Midtrans
            $transaksi = TransaksiOnline::create([
                'user_id' => $user->id,
                'kode_transaksi' => $kode,
                'tanggal' => now(),
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => 'pending', // Status awal selalu pending
                'status_transaksi' => 'diproses',
                'catatan' => $request->catatan,
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
                'total' => $total, // Total sudah dihitung di sini
            ]);
            Log::info('TransaksiOnline awal berhasil dibuat dengan kode: ' . $transaksi->kode_transaksi);

            // Simpan detail transaksi dan kurangi stok di sini juga
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
                        Log::error("Stok tidak cukup untuk produk {$produk->nama_produk}. Stok tersedia: {$produk->stok}, Diminta: {$jumlahUtama}.");
                        return redirect()->back()->withInput()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }

                    $produk->decrement('stok', $jumlahUtama);
                    Log::info("Stok produk '{$produk->nama_produk}' dikurangi sebanyak {$jumlahUtama} unit utama.");

                    Stok::create([
                        'produk_id' => $produk->id,
                        'satuan_id' => $satuanId,
                        'jenis' => 'keluar',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Transaksi online #' . $kode,
                    ]);
                    Log::info("Catatan stok keluar untuk produk '{$produk->nama_produk}' berhasil dibuat.");
                }

                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'jumlah_json' => $jumlahArr,
                    'harga_json' => $hargaArr,
                    'subtotal' => $subtotalProduk,
                ]);
                Log::info("Detail transaksi untuk produk '{$produk->nama_produk}' berhasil disimpan.");
            }

            // Bersihkan sesi form_cepat_data setelah data diambil dan diproses
            session()->forget('form_cepat_data');
            Log::info('Sesi form_cepat_data dibersihkan.');

            // Jika metode pembayaran BUKAN payment_gateway, commit transaksi di sini
            if ($request->metode_pembayaran !== 'payment_gateway') {
                DB::commit();
                Log::info('Transaksi database berhasil di-commit untuk pembayaran non-gateway.');
                Artisan::call('produk:update-dailyusage-rop');
                Log::info('Perintah Artisan produk:update-dailyusage-rop dijalankan.');
                return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
            }

            // Jika metode pembayaran adalah payment_gateway, lanjutkan ke Midtrans
            Log::info('Memproses pembayaran dengan Midtrans (payment_gateway).');
            $customer = [
                'first_name' => $user->nama,
                'email' => $user->email,
                'phone' => $user->no_hp,
            ];
            $items = [[
                'id' => $kode,
                'price' => $total,
                'quantity' => 1,
                'name' => 'Total Belanja KZ Family'
            ]];
            $custom_fields = [
                'user_id' => $user->id,
                'metode_pembayaran' => $request->metode_pembayaran,
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->alamat_pengambilan,
                'catatan' => $request->catatan,
            ];

            // Panggil Midtrans Snap dengan orderId yang sudah dibuat
            $snapToken = \App\Helpers\MidtransSnap::generateSnapToken($kode, $total, $customer, $items, $custom_fields);
            Log::info('Snap Token Midtrans berhasil digenerate.');

            // Commit transaksi setelah Snap Token berhasil digenerate
            DB::commit();
            Log::info('Transaksi database berhasil di-commit setelah Snap Token digenerate.');

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $kode,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat membuat pesanan (catch block): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
        Log::info('Metode store (dari keranjang) berhasil diakses.');
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

        DB::beginTransaction(); // Mulai transaksi database di sini
        try {
            $total = 0;
            $kode = 'TX-ON-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
            $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

            // Hitung total harga dari keranjangs
            foreach ($keranjangs as $item) {
                $produk = $item->produk;
                $jumlahArr = $item->jumlah_json;

                foreach ($jumlahArr as $satuanId => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;

                    $satuan = Satuan::findOrFail($satuanId);
                    $harga = HargaProduk::where('produk_id', $produk->id)
                        ->where('satuan_id', $satuanId)
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->value('harga') ?? 0;

                    $total += $harga * $qty;
                }
            }

            // 🔥 Buat entri TransaksiOnline di database SEBELUM memanggil Midtrans
            $transaksi = TransaksiOnline::create([
                'user_id' => $user->id,
                'kode_transaksi' => $kode,
                'tanggal' => now(),
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => 'pending', // Status awal selalu pending
                'status_transaksi' => 'diproses',
                'catatan' => $request->catatan,
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
                'total' => $total, // Total sudah dihitung di sini
            ]);
            Log::info('TransaksiOnline awal berhasil dibuat dengan kode: ' . $transaksi->kode_transaksi);

            // Simpan detail transaksi dan kurangi stok di sini juga
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
                        Log::error("Stok tidak cukup untuk produk {$produk->nama_produk}. Stok tersedia: {$produk->stok}, Diminta: {$jumlahUtama}.");
                        return redirect()->back()->withInput()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }

                    $produk->decrement('stok', $jumlahUtama);
                    Log::info("Stok produk '{$produk->nama_produk}' dikurangi sebanyak {$jumlahUtama} unit utama.");

                    Stok::create([
                        'produk_id' => $produk->id,
                        'satuan_id' => $satuanId,
                        'jenis' => 'keluar',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Transaksi online #' . $kode,
                    ]);
                    Log::info("Catatan stok keluar untuk produk '{$produk->nama_produk}' berhasil dibuat.");
                }

                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'jumlah_json' => $jumlahArr,
                    'harga_json' => $hargaArr,
                    'subtotal' => $subtotalProduk,
                ]);
                Log::info("Detail transaksi untuk produk '{$produk->nama_produk}' berhasil disimpan.");
            }

            // Bersihkan keranjang pengguna setelah data diambil dan diproses
            $user->keranjangs()->whereIn('id', $keranjangIds)->delete();
            session()->forget('keranjang_ids');
            Log::info('Keranjang pengguna dibersihkan.');


            // Jika metode pembayaran BUKAN payment_gateway, commit transaksi di sini
            if ($request->metode_pembayaran !== 'payment_gateway') {
                DB::commit();
                Log::info('Transaksi database berhasil di-commit untuk pembayaran non-gateway.');
                Artisan::call('produk:update-dailyusage-rop');
                Log::info('Perintah Artisan produk:update-dailyusage-rop dijalankan.');
                return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
            }

            // Jika metode pembayaran adalah payment_gateway, lanjutkan ke Midtrans
            Log::info('Memproses pembayaran dengan Midtrans dari keranjang (payment_gateway).');
            $customer = [
                'first_name' => $user->nama,
                'email' => $user->email,
                'phone' => $user->no_hp,
            ];
            $items = [[
                'id' => $kode,
                'price' => $total,
                'quantity' => 1,
                'name' => 'Total Belanja KZ Family'
            ]];
            $custom_fields = [
                'user_id' => $user->id,
                'metode_pembayaran' => $request->metode_pembayaran,
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->alamat_pengambilan,
                'catatan' => $request->catatan,
            ];

            // Panggil Midtrans Snap dengan orderId yang sudah dibuat
            $snapToken = \App\Helpers\MidtransSnap::generateSnapToken($kode, $total, $customer, $items, $custom_fields);
            Log::info('Snap Token Midtrans berhasil digenerate dari keranjang.');

            // Commit transaksi setelah Snap Token berhasil digenerate
            DB::commit();
            Log::info('Transaksi database berhasil di-commit setelah Snap Token digenerate.');

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $kode,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Terjadi kesalahan saat membuat pesanan (catch block): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }


    /**
     * Metode privat untuk memproses transaksi yang tidak menggunakan payment gateway.
     * Metode ini sekarang tidak lagi dipanggil untuk membuat transaksi,
     * melainkan hanya untuk mengupdate status dan membersihkan jika diperlukan
     * (namun, alur ini sudah di-inline ke formBelanjaCepatStore dan store).
     * Metode ini bisa dihapus atau diadaptasi jika masih ada kebutuhan lain.
     */
    private function prosesTransaksiSelesai($itemsData, $user, $kode, Request $request, $jenisPelanggan, $flowType)
    {
        // Logika di metode ini sudah dipindahkan ke formBelanjaCepatStore dan store
        // Anda bisa menghapus metode ini jika tidak ada panggilan lain
        // atau mengadaptasinya untuk tujuan lain jika diperlukan.
        Log::warning('Metode prosesTransaksiSelesai dipanggil, namun logikanya sudah di-inline ke store methods.');
        return redirect()->route('mobile.home.index')->with('warning', 'Alur transaksi sudah diperbarui.');
    }
}
