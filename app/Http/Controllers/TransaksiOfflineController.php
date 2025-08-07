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
use Illuminate\Support\Facades\Log;
use App\Helpers\MidtransSnap;




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

        // Filter by metode_pembayaran (BARU DITAMBAHKAN)
        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        $transaksi = $query->get();

        // Ambil nilai filter yang dipilih untuk dikirim kembali ke view
        $filterMetodePembayaran = $request->metode_pembayaran;


        return view('transaksi_offline.index', compact('transaksi', 'pelanggans', 'filterMetodePembayaran'));
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
        $produk = Produk::with(['satuans', 'hargaProduks'])->get();
        $pelanggans = User::where('role', 'pelanggan')->get();
        $kode_transaksi = 'TX-OFF-' . now()->format('ymd-His') . '-' . strtoupper(Str::random(4));
        $tanggal = now();

        return view('transaksi_offline.create', compact('produk', 'pelanggans', 'kode_transaksi', 'tanggal'));
    }

    public function store(Request $request)
    {
        Log::info('Request input for TransaksiOffline store:', $request->all());

        $rules = [
            'kode_transaksi' => 'required|unique:transaksi_offline,kode_transaksi',
            'tanggal' => 'required|date',
            'jenis_pelanggan' => 'required|in:Individu,Toko Kecil',
            'total' => 'required|numeric',
            'pelanggan_id' => 'nullable|exists:users,id',
            'produk_id.*' => 'required|exists:produks,id',
            'jumlah_json.*' => 'required|string', // String karena dikirim sebagai JSON string
            'harga_json.*' => 'required|string',   // String karena dikirim sebagai JSON string
            'metode_pembayaran' => 'required|in:cash,payment_gateway',
        ];

        // Validasi khusus untuk metode pembayaran 'cash'
        if ($request->metode_pembayaran === 'cash') {
            $rules['dibayar'] = 'required|numeric';
            $rules['kembalian'] = 'required|numeric';
        }

        $request->validate($rules);

        $sanitizeMoney = fn($val) => floatval(str_replace(['.', ','], ['', '.'], $val));
        $totalTransaksi = $sanitizeMoney($request->total);
        $kodeTransaksi = $request->kode_transaksi;
        $jenisPelanggan = $request->jenis_pelanggan;
        $pelangganId = $request->pelanggan_id;
        $tanggalTransaksi = $request->tanggal; // Carbon instance jika dari form, atau string

        $itemDetailsForMidtrans = []; // Untuk payload Midtrans Snap
        $produkDataProcessed = []; // Data produk yang sudah di-parse untuk disimpan ke detail transaksi

        // Loop untuk memproses produk dan menyiapkan data
        foreach ($request->produk_id as $i => $produkId) {
            $jumlahArr = json_decode($request->jumlah_json[$i], true);
            $hargaArr = json_decode($request->harga_json[$i], true);

            // Validasi dasar JSON dan array
            if (
                !is_array($jumlahArr) || json_last_error() !== JSON_ERROR_NONE ||
                !is_array($hargaArr) || json_last_error() !== JSON_ERROR_NONE
            ) {
                Log::warning("Skipping invalid JSON data for produk_id {$produkId}. Jumlah: {$request->jumlah_json[$i]}, Harga: {$request->harga_json[$i]}");
                continue; // Lewati item jika JSON tidak valid
            }

            $produk = Produk::findOrFail($produkId);
            $subtotalProduk = 0;
            $totalJumlahUtamaProduk = 0; // Total kuantitas produk ini dalam satuan utama

            foreach ($jumlahArr as $satuanId => $qty) {
                $qty = floatval($qty);
                if ($qty <= 0) continue; // Hindari kuantitas nol atau negatif

                $satuan = \App\Models\Satuan::find($satuanId);
                if (!$satuan) {
                    Log::warning("Satuan ID {$satuanId} not found for Produk ID {$produkId}. Skipping this quantity.");
                    continue;
                }

                $hargaSatuan = $sanitizeMoney($hargaArr[$satuanId] ?? 0);
                $subtotalProduk += $qty * $hargaSatuan;

                $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                $totalJumlahUtamaProduk += $qty * $konversi;

                // Siapkan itemDetails untuk Midtrans
                $itemDetailsForMidtrans[] = [
                    'id' => $produk->id . '-' . $satuan->id,
                    'price' => (int) round($hargaSatuan), // Midtrans butuh integer
                    'quantity' => (int) $qty,
                    'name' => substr($produk->nama_produk . ' (' . $satuan->nama_satuan . ')', 0, 50), // Hindari nama terlalu panjang
                ];
            }

            // Simpan data produk yang sudah diproses untuk detail transaksi
            $produkDataProcessed[] = [
                'produk_id' => $produkId,
                'jumlah_json' => $jumlahArr, // Biarkan sebagai array untuk disimpan ke DB
                'harga_json' => $hargaArr,   // Biarkan sebagai array untuk disimpan ke DB
                'subtotal' => $subtotalProduk,
                'total_jumlah_utama' => $totalJumlahUtamaProduk, // Simpan ini untuk pengurangan stok
            ];
        }

        DB::beginTransaction();
        try {
            // --- BAGIAN BARU: BUAT TRANSAKSI OFFLINE DENGAN STATUS PENDING/LUNAS ---
            $transaksi = TransaksiOffline::create([
                'kode_transaksi' => $kodeTransaksi,
                'tanggal' => $tanggalTransaksi,
                'jenis_pelanggan' => $jenisPelanggan,
                'total' => $totalTransaksi,
                'dibayar' => ($request->metode_pembayaran === 'cash') ? $sanitizeMoney($request->dibayar) : 0, // Hanya diisi jika cash
                'kembalian' => ($request->metode_pembayaran === 'cash') ? $sanitizeMoney($request->kembalian) : 0, // Hanya diisi jika cash
                'pelanggan_id' => $pelangganId,
                'metode_pembayaran' => $request->metode_pembayaran,
                'snap_token' => null, // Akan diisi jika payment_gateway
                'payment_type' => null, // Akan diisi oleh webhook
                'status_pembayaran' => ($request->metode_pembayaran === 'payment_gateway') ? 'pending' : 'lunas',
            ]);
            Log::info('TransaksiOffline awal berhasil dibuat dengan kode: ' . $transaksi->kode_transaksi . ' dengan status: ' . $transaksi->status_pembayaran);

            // Simpan detail transaksi dan lakukan pengecekan stok
            foreach ($produkDataProcessed as $item) {
                $produk = Produk::findOrFail($item['produk_id']);
                $totalJumlahUtama = $item['total_jumlah_utama'];

                // Cek stok sebelum membuat detail transaksi
                if ($produk->stok < $totalJumlahUtama) {
                    DB::rollBack();
                    Log::error("Stok tidak cukup untuk produk {$produk->nama_produk}. Stok tersedia: {$produk->stok}, Diminta: {$totalJumlahUtama}.");
                    return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                }

                TransaksiOfflineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah_json' => $item['jumlah_json'],
                    'harga_json' => $item['harga_json'],
                    'subtotal' => $item['subtotal'],
                ]);
                Log::info("Detail transaksi offline untuk produk '{$produk->nama_produk}' berhasil disimpan.");

                // KURANGI STOK HANYA UNTUK METODE PEMBAYARAN 'cash' DI SINI
                // Untuk payment_gateway, pengurangan stok akan dilakukan di webhook setelah pembayaran sukses.
                if ($request->metode_pembayaran === 'cash') {
                    $produk->decrement('stok', $totalJumlahUtama);
                    Log::info("Stok produk '{$produk->nama_produk}' dikurangi sebanyak {$totalJumlahUtama} unit utama (cash).");

                    Stok::create([
                        'produk_id' => $item['produk_id'],
                        'jenis' => 'keluar',
                        'jumlah' => $totalJumlahUtama,
                        'keterangan' => 'Transaksi penjualan offline (cash) ' . $transaksi->kode_transaksi,
                    ]);
                    Log::info("Catatan stok keluar untuk produk '{$produk->nama_produk}' berhasil dibuat (cash).");
                }
            }

            // Tambahkan catatan keuangan hanya jika metode pembayaran 'cash'
            if ($request->metode_pembayaran === 'cash') {
                Keuangan::create([
                    'transaksi_id' => $transaksi->id,
                    'tanggal' => $tanggalTransaksi,
                    'jenis' => 'pemasukan',
                    'nominal' => $transaksi->total,
                    'keterangan' => 'Pemasukan dari transaksi offline (cash) #' . $transaksi->kode_transaksi,
                    'sumber' => 'offline',
                ]);
                Log::info('Catatan keuangan untuk transaksi offline (cash) berhasil dibuat.');
            }

            DB::commit(); // Commit transaksi database
            Log::info('Transaksi database berhasil di-commit.');

            // === Payment Gateway Flow ===
            if ($request->metode_pembayaran === 'payment_gateway') {
                $customer = [
                    'first_name' => 'Pelanggan Offline',
                    'email' => 'offline@example.com', // Email default
                    'phone' => '081234567890', // Nomor telepon default
                ];

                if ($pelangganId && $userPelanggan = User::find($pelangganId)) {
                    $customer['first_name'] = $userPelanggan->nama;
                    $customer['email'] = $userPelanggan->email ?? $customer['email']; // Gunakan default jika kosong
                    $customer['phone'] = $userPelanggan->no_hp ?? $customer['phone']; // Gunakan default jika kosong
                }

                // CUSTOM FIELDS: Kirim data minimal untuk identifikasi di webhook
                // TIDAK PERLU KIRIM PRODUK DATA RAW LAGI
                $custom_fields = [
                    'transaksi_type' => 'offline', // PENTING: Untuk membedakan di webhook
                    'transaksi_id_local' => $transaksi->id, // ID transaksi offline lokal
                    'pelanggan_id' => $pelangganId,
                    'jenis_pelanggan' => $jenisPelanggan,
                ];

                $snapToken = MidtransSnap::generateSnapToken(
                    $kodeTransaksi, // Ini akan jadi order_id Midtrans
                    $totalTransaksi,
                    $customer,
                    $itemDetailsForMidtrans,
                    $custom_fields
                );
                Log::info('Snap Token Midtrans berhasil digenerate untuk transaksi offline.');

                // Update snap_token di TransaksiOffline yang sudah dibuat
                $transaksi->update(['snap_token' => $snapToken]);

                return response()->json([
                    'snap_token' => $snapToken,
                    'order_id' => $kodeTransaksi,
                    'total' => $totalTransaksi,
                    'redirect_url' => route('transaksi_offline.index')
                ]);
            } else {
                // === Cash Flow ===
                return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil disimpan.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan transaksi offline: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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

            DB::commit();

            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
