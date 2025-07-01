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
use App\Models\Keuangan; // Keuangan tidak digunakan di sini, bisa dihapus jika tidak ada relevansi
use App\Models\Stok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use App\Models\User; // User tidak digunakan secara langsung di sini, bisa dihapus jika tidak ada relevansi
use Illuminate\Support\Facades\Log;
use App\Helpers\MidtransSnap; // Pastikan helper ini di-import

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

        // Simpan data produk ke sesi untuk digunakan di formBelanjaCepatStore
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

        $total = 0;
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';
        $itemDetailsForMidtrans = []; // Inisialisasi di luar kondisi agar selalu tersedia

        // Hitung total harga dan siapkan itemDetails untuk Midtrans (jika diperlukan)
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

                // Siapkan itemDetails untuk Midtrans
                $itemDetailsForMidtrans[] = [
                    'id' => $produk->id . '-' . $satuan->id, // ID unik untuk setiap item + satuan
                    'price' => (int) $harga, // Harga per unit
                    'quantity' => (int) $qty, // Kuantitas unit
                    'name' => $produk->nama_produk . ' (' . $satuan->nama_satuan . ')',
                ];
            }
        }

        // Generate kode transaksi awal (akan jadi final untuk non-gateway, sementara untuk gateway)
        $kode = 'TX-ON-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));

        if ($request->metode_pembayaran === 'payment_gateway') {
            Log::info('Memproses pembayaran dengan Midtrans (payment_gateway) dari form cepat.');

            // Untuk Payment Gateway, kita HANYA generate snap token dan TIDAK menyimpan transaksi ke DB dulu.
            // Data keranjang/form cepat akan tetap utuh sampai webhook Midtrans diterima.

            $customer = [
                'first_name' => $user->nama,
                'email' => $user->email,
                'phone' => $user->no_hp,
            ];

            // Data penting yang akan dikirim kembali melalui webhook Midtrans
            $custom_fields = [
                'user_id' => $user->id,
                'metode_pembayaran_app' => $request->metode_pembayaran, // Menggunakan nama berbeda agar tidak konflik dengan payment_type Midtrans
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
                'catatan' => $request->catatan,
                'jenis_pelanggan' => $jenisPelanggan,
                'produk_data_raw' => json_encode($produkData), // Simpan data mentah produk/kuantitas
            ];

            // Panggil Midtrans Snap
            $snapToken = MidtransSnap::generateSnapToken($kode, $total, $customer, $itemDetailsForMidtrans, $custom_fields);
            Log::info('Snap Token Midtrans berhasil digenerate dari form cepat.');

            // Tidak ada DB::beginTransaction() atau DB::commit() di sini untuk transaksi utama
            // karena transaksi baru dibuat di webhook setelah pembayaran sukses.

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $kode,
            ]);
        } else {
            // --- ALUR UNTUK NON-PAYMENT GATEWAY (COD, Bayar di Toko) ---
            DB::beginTransaction(); // Mulai transaksi database di sini
            try {
                // Buat entri TransaksiOnline di database
                $transaksi = TransaksiOnline::create([
                    'user_id' => $user->id,
                    'kode_transaksi' => $kode,
                    'tanggal' => now(),
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'status_pembayaran' => ($request->metode_pembayaran === 'bayar_di_toko') ? 'lunas' : 'pending',
                    'status_transaksi' => 'diproses',
                    'catatan' => $request->catatan,
                    'metode_pengambilan' => $request->metode_pengambilan,
                    'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
                    'total' => $total,
                ]);
                Log::info('TransaksiOnline awal berhasil dibuat dengan kode (non-gateway): ' . $transaksi->kode_transaksi);

                // Simpan detail transaksi dan kurangi stok
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

                DB::commit();
                Log::info('Transaksi database berhasil di-commit untuk pembayaran non-gateway.');
                return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Terjadi kesalahan saat membuat pesanan (catch block, non-gateway): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return redirect()->back()->withInput()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
            }
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

        $total = 0;
        $kode = 'TX-ON-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';
        $itemDetailsForMidtrans = []; // Inisialisasi di luar kondisi agar selalu tersedia

        // Hitung total harga dari keranjangs dan siapkan itemDetails untuk Midtrans (jika diperlukan)
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

                // Siapkan itemDetails untuk Midtrans
                $itemDetailsForMidtrans[] = [
                    'id' => $produk->id . '-' . $satuan->id, // ID unik untuk setiap item + satuan
                    'price' => (int) $harga, // Harga per unit
                    'quantity' => (int) $qty, // Kuantitas unit
                    'name' => $produk->nama_produk . ' (' . $satuan->nama_satuan . ')',
                ];
            }
        }

        if ($request->metode_pembayaran === 'payment_gateway') {
            Log::info('Memproses pembayaran dengan Midtrans dari keranjang (payment_gateway).');
            // Untuk Payment Gateway, kita HANYA generate snap token dan TIDAK menyimpan transaksi ke DB dulu.
            // Data keranjang akan tetap utuh sampai webhook Midtrans diterima.

            $customer = [
                'first_name' => $user->nama,
                'email' => $user->email,
                'phone' => $user->no_hp,
            ];

            // Data penting yang akan dikirim kembali melalui webhook Midtrans
            $custom_fields = [
                'user_id' => $user->id,
                'metode_pembayaran_app' => $request->metode_pembayaran, // Menggunakan nama berbeda
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
                'catatan' => $request->catatan,
                'jenis_pelanggan' => $jenisPelanggan,
                'keranjang_ids_raw' => json_encode($keranjangIds), // Simpan ID keranjang yang dipilih
            ];

            // Panggil Midtrans Snap
            $snapToken = MidtransSnap::generateSnapToken($kode, $total, $customer, $itemDetailsForMidtrans, $custom_fields);
            Log::info('Snap Token Midtrans berhasil digenerate dari keranjang.');

            // Tidak ada DB::beginTransaction() atau DB::commit() di sini untuk transaksi utama
            // karena transaksi baru dibuat di webhook setelah pembayaran sukses.

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $kode,
            ]);
        } else {
            // --- ALUR UNTUK NON-PAYMENT GATEWAY (COD, Bayar di Toko) ---
            DB::beginTransaction(); // Mulai transaksi database di sini
            try {
                // Buat entri TransaksiOnline di database
                $transaksi = TransaksiOnline::create([
                    'user_id' => $user->id,
                    'kode_transaksi' => $kode,
                    'tanggal' => now(),
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'status_pembayaran' => ($request->metode_pembayaran === 'bayar_di_toko') ? 'lunas' : 'pending',
                    'status_transaksi' => 'diproses',
                    'catatan' => $request->catatan,
                    'metode_pengambilan' => $request->metode_pengambilan,
                    'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
                    'total' => $total,
                ]);
                Log::info('TransaksiOnline awal berhasil dibuat dengan kode (non-gateway): ' . $transaksi->kode_transaksi);

                // Simpan detail transaksi dan kurangi stok
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

                DB::commit();
                Log::info('Transaksi database berhasil di-commit untuk pembayaran non-gateway.');

                return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Terjadi kesalahan saat membuat pesanan (catch block, non-gateway): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return redirect()->back()->withInput()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
            }
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
