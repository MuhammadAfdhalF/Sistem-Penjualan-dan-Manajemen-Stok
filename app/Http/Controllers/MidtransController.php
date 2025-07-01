<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tidak selalu perlu di webhook, tapi biarkan jika ada konteks lain
use Illuminate\Support\Str; // Tidak selalu perlu di webhook, tapi biarkan
use App\Models\TransaksiOnline;
use Midtrans\Snap; // Tidak perlu Snap::getSnapToken di webhook
use Midtrans\Config; // Perlu untuk validasi notifikasi
use Illuminate\Support\Facades\Log;
use App\Models\Stok;
use App\Models\TransaksiOnlineDetail;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Satuan;
use App\Models\HargaProduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\User; // Import model User untuk mengambil detail user

class MidtransController extends Controller
{
    public function sukses()
    {
        return view('mobile.midtrans_sukses', [
            'activeMenu' => null
        ]);
    }

    // Metode getSnapToken ini sekarang hanya menerima data yang sudah siap dari ProsesTransaksiController
    // Metode ini tidak perlu diubah karena sudah sesuai dengan alur baru:
    // Hanya menerima data, menyiapkan parameter Midtrans, dan mengembalikan snap token.
    public function getSnapToken(Request $request)
    {
        // 🔥 LOG PALING AWAL UNTUK DEBUGGING REQUEST DATA
        Log::info('getSnapToken: Request diterima. Method: ' . $request->method() . ', URL: ' . $request->fullUrl());
        Log::info('getSnapToken: Raw request body: ' . $request->getContent());
        Log::info('getSnapToken: Request input all: ' . json_encode($request->all()));


        // Validasi input dari frontend
        $request->validate([
            'total' => 'required|numeric|min:1',
            'metode_pembayaran' => 'required|string',
            'metode_pengambilan' => 'required|string',
            'alamat_pengambilan' => 'nullable|string',
            'catatan' => 'nullable|string',
            'order_id' => 'required|string', // Order ID yang sudah dibuat di ProsesTransaksiController
            'item_details' => 'required|string', // Validasi sebagai string karena dikirim sebagai JSON.stringify
        ]);

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false) === 'true';
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = $request->order_id;
        $totalFinal = $request->total;

        // 🔥 LOGGING UNTUK DEBUGGING ITEM_DETAILS YANG DITERIMA
        Log::info('getSnapToken: item_details dari request: ' . $request->item_details);
        Log::info('getSnapToken: Type of item_details in request: ' . gettype($request->item_details));

        $itemDetails = $request->item_details; // Ambil itemDetails langsung dari request

        // Jika itemDetails diterima sebagai string JSON, decode dulu
        if (is_string($itemDetails)) {
            $itemDetails = json_decode($itemDetails, true);
            // Cek apakah decode berhasil
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('getSnapToken: Gagal decode item_details JSON: ' . json_last_error_msg());
                $itemDetails = []; // Fallback jika decode gagal
            } else {
                Log::info('getSnapToken: item_details berhasil di-decode dari string: ' . json_encode($itemDetails));
            }
        }

        // Pastikan itemDetails adalah array setelah decoding
        if (!is_array($itemDetails)) {
            Log::error('getSnapToken: item_details bukan array setelah decoding. Menggunakan item ringkasan.');
            $itemDetails = []; // Fallback ke array kosong jika decoding gagal
        }

        Log::info('getSnapToken: Menerima request untuk OrderID: ' . $orderId);
        Log::info('getSnapToken: ItemDetails yang akan dikirim ke Midtrans: ' . json_encode($itemDetails));

        // Jika itemDetails kosong (misal tidak ada produk di transaksi), tambahkan item ringkasan
        if (empty($itemDetails)) {
            $itemDetails[] = [
                'id' => $orderId,
                'price' => (int) $totalFinal,
                'quantity' => 1,
                'name' => 'Total Belanja KZ Family'
            ];
            Log::warning('getSnapToken: Item details kosong, mengirim item ringkasan untuk OrderID: ' . $orderId);
        }


        // Data customer untuk Midtrans Snap
        $customer = [
            'first_name' => Auth::user()->nama,
            'email' => Auth::user()->email,
            'phone' => Auth::user()->no_hp,
        ];

        // custom_fields akan digunakan di webhook untuk merekonstruksi transaksi
        // Pastikan nama field unik dan tidak konflik dengan field Midtrans lainnya
        $custom_fields = [
            'user_id' => Auth::id(),
            'metode_pembayaran_app' => $request->metode_pembayaran, // Menggunakan nama berbeda agar tidak konflik dengan payment_type Midtrans
            'metode_pengambilan' => $request->metode_pengambilan,
            'alamat_pengambilan' => $request->alamat_pengambilan,
            'catatan' => $request->catatan,
            // 'produk_data_raw' atau 'keranjang_ids_raw' akan ditambahkan di ProsesTransaksiController
            // karena data ini berasal dari sana.
        ];

        // Persiapkan parameter Snap
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $totalFinal,
            ],
            'customer_details' => $customer,
            'item_details' => $itemDetails, // Menggunakan detail produk yang sebenarnya
            'callbacks' => [
                'finish' => route('mobile.pesanan.sukses'),
                'error' => route('mobile.home.index'),
                'pending' => route('mobile.home.index'),
            ],
            // Midtrans hanya support custom_field1, custom_field2, custom_field3
            // Kita akan encode custom_fields kita ke dalam salah satunya
            'custom_field1' => json_encode($custom_fields)
        ];

        // Dapatkan token Snap dari Midtrans
        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'token' => $snapToken,
            'order_id' => $orderId
        ]);
    }


    public function handleWebhook(Request $request)
    {
        Log::info('Webhook Midtrans diterima.');
        $notif = file_get_contents("php://input");
        $data = json_decode($notif);

        $orderId = $data->order_id ?? null;
        $transactionStatus = $data->transaction_status ?? null;
        $fraudStatus = $data->fraud_status ?? null;
        $grossAmount = $data->gross_amount ?? 0;
        $paymentType = $data->payment_type ?? null;

        Log::info("Webhook data: OrderID={$orderId}, Status={$transactionStatus}, FraudStatus={$fraudStatus}");

        if (!$orderId || !$transactionStatus) {
            Log::warning('Webhook: OrderID atau TransactionStatus kosong.', ['data' => $data]);
            return response()->json(['status' => 'invalid'], 400);
        }

        // Konfigurasi Midtrans untuk verifikasi notifikasi (disarankan untuk keamanan)
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false) === 'true';
        Config::$isSanitized = true;
        Config::$is3ds = true;

        DB::beginTransaction();
        try {
            // Cari transaksi yang mungkin sudah ada di database Anda
            $transaksi = TransaksiOnline::where('kode_transaksi', $orderId)->first();

            // Jika transaksi sudah lunas, langsung return OK untuk idempotency
            if ($transaksi && $transaksi->status_pembayaran === 'lunas') {
                Log::info('Webhook: Transaksi sudah lunas, tidak perlu update lagi.', ['order_id' => $orderId]);
                DB::commit();
                return response()->json(['status' => 'already paid'], 200);
            }

            $processCreationAndStock = false; // Flag untuk membuat transaksi dan mengurangi stok
            $updateStatusOnly = false; // Flag untuk hanya mengupdate status transaksi yang sudah ada

            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'accept') {
                        $processCreationAndStock = true;
                    } else {
                        $updateStatusOnly = true; // Gagal karena fraud
                    }
                }
            } elseif ($transactionStatus == 'settlement') {
                $processCreationAndStock = true;
            } elseif ($transactionStatus == 'pending') {
                // Jika transaksi belum ada, tidak ada aksi krusial.
                // Jika transaksi sudah ada (misal dari pending sebelumnya), kita bisa update statusnya.
                if ($transaksi) {
                    $updateStatusOnly = true;
                } else {
                    Log::info('Webhook: Transaksi pending diterima dan belum ada di DB. Tidak ada aksi yang diambil saat ini.');
                    DB::commit(); // Commit transaksi kosong jika tidak ada yang dilakukan
                    return response()->json(['status' => 'ok']);
                }
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                $updateStatusOnly = true; // Gagal
            }

            // --- Logika Kreasi Transaksi & Pengurangan Stok (HANYA UNTUK PAYMENT GATEWAY) ---
            if ($processCreationAndStock) {
                if (!$transaksi) { // Transaksi belum ada di DB, berarti ini yang pertama kali terkonfirmasi LUNAS
                    Log::info('Webhook: Pembayaran LUNAS, membuat TransaksiOnline baru dan mengurangi stok.');

                    // Dapatkan data yang dikirim di custom_fields
                    $customFields = json_decode($data->custom_field1 ?? '{}', true); // Ambil dari custom_field1

                    $userId = $customFields['user_id'] ?? null;
                    $metodePembayaranApp = $customFields['metode_pembayaran_app'] ?? 'payment_gateway';
                    $metodePengambilan = $customFields['metode_pengambilan'] ?? 'ambil di toko';
                    $alamatPengambilan = $customFields['alamat_pengambilan'] ?? null;
                    $catatan = $customFields['catatan'] ?? null;
                    $jenisPelanggan = $customFields['jenis_pelanggan'] ?? 'Individu';

                    if (!$userId) {
                        Log::error('Webhook: User ID tidak ditemukan di custom_fields.', ['data' => $data]);
                        DB::rollBack();
                        return response()->json(['status' => 'error', 'message' => 'User ID not found'], 400);
                    }

                    $user = User::find($userId);
                    if (!$user) {
                        Log::error('Webhook: User tidak ditemukan di database.', ['user_id' => $userId]);
                        DB::rollBack();
                        return response()->json(['status' => 'error', 'message' => 'User not found'], 400);
                    }

                    $produkItems = [];
                    $keranjangIdsToClear = [];

                    // Rekonstruksi produk data dari custom_fields
                    if (isset($customFields['produk_data_raw'])) {
                        $produkItems = json_decode($customFields['produk_data_raw'], true);
                    } elseif (isset($customFields['keranjang_ids_raw'])) {
                        $keranjangIdsToClear = json_decode($customFields['keranjang_ids_raw'], true);
                        // Ambil produk dari keranjang berdasarkan ID yang disimpan
                        $keranjangsFromDb = Keranjang::with('produk.satuans', 'produk.hargaProduks')
                            ->where('user_id', $user->id)
                            ->whereIn('id', $keranjangIdsToClear)
                            ->get();

                        foreach ($keranjangsFromDb as $kItem) {
                            $produkItems[] = [
                                'produk_id' => $kItem->produk_id,
                                'jumlah_json' => $kItem->jumlah_json,
                            ];
                        }
                    }

                    if (empty($produkItems)) {
                        Log::error('Webhook: Produk data tidak ditemukan dari custom_fields atau keranjang.', ['data' => $data]);
                        DB::rollBack();
                        return response()->json(['status' => 'error', 'message' => 'Product data not found'], 400);
                    }

                    // Buat TransaksiOnline baru
                    $transaksi = TransaksiOnline::create([
                        'user_id' => $userId,
                        'kode_transaksi' => $orderId, // Gunakan orderId dari Midtrans
                        'tanggal' => now(),
                        'metode_pembayaran' => $metodePembayaranApp,
                        'snap_token' => $data->snap_token ?? null, // Simpan snap token jika ada
                        'payment_type' => $paymentType,
                        'status_pembayaran' => 'lunas',
                        'status_transaksi' => 'diproses',
                        'total' => $grossAmount, // Gunakan gross_amount dari Midtrans (total final)
                        'catatan' => $catatan,
                        'alamat_pengambilan' => $alamatPengambilan,
                        'metode_pengambilan' => $metodePengambilan,
                    ]);
                    Log::info('TransaksiOnline berhasil dibuat via webhook dengan kode: ' . $transaksi->kode_transaksi);

                    // Simpan detail transaksi dan kurangi stok
                    foreach ($produkItems as $item) {
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

                            // Cek stok kembali (opsional, tapi baik untuk validasi akhir)
                            if ($produk->stok < $jumlahUtama) {
                                Log::error("Webhook: Stok tidak cukup saat mencoba mengurangi stok untuk transaksi {$orderId}. Produk: {$produk->nama_produk}. Stok tersedia: {$produk->stok}, Diminta: {$jumlahUtama}. Transaksi akan tetap dibuat, tapi stok tidak akurat.");
                                // Anda bisa memilih untuk DB::rollBack() di sini jika stok tidak cukup,
                                // tapi itu akan membatalkan seluruh transaksi yang baru dibuat.
                                // Atau, Anda bisa menandai transaksi ini sebagai 'perlu perhatian manual'.
                            } else {
                                $produk->decrement('stok', $jumlahUtama);
                                Log::info("Webhook: Stok produk '{$produk->nama_produk}' dikurangi sebanyak {$jumlahUtama} unit utama.");

                                Stok::create([
                                    'produk_id' => $produk->id,
                                    'satuan_id' => $satuanId,
                                    'jenis' => 'keluar',
                                    'jumlah' => $jumlahUtama,
                                    'keterangan' => 'Transaksi online via Midtrans #' . $orderId,
                                ]);
                                Log::info("Webhook: Catatan stok keluar untuk produk '{$produk->nama_produk}' berhasil dibuat.");
                            }
                        }

                        TransaksiOnlineDetail::create([
                            'transaksi_id' => $transaksi->id,
                            'produk_id' => $produk->id,
                            'jumlah_json' => $jumlahArr,
                            'harga_json' => $hargaArr,
                            'subtotal' => $subtotalProduk,
                        ]);
                        Log::info("Webhook: Detail transaksi untuk produk '{$produk->nama_produk}' berhasil disimpan.");
                    }

                    // Bersihkan keranjang pengguna jika transaksi berasal dari keranjang
                    if (!empty($keranjangIdsToClear)) {
                        Keranjang::where('user_id', $user->id)->whereIn('id', $keranjangIdsToClear)->delete();
                        Log::info('Webhook: Keranjang pengguna dibersihkan.');
                    }

                    Artisan::call('produk:update-dailyusage-rop');
                    Log::info('Webhook: Perintah Artisan produk:update-dailyusage-rop dijalankan.');
                } else {
                    // Transaksi sudah ada di DB (misal dari status pending sebelumnya)
                    // Cukup update status pembayaran saja
                    $transaksi->update([
                        'status_pembayaran' => 'lunas',
                        'status_transaksi' => 'diproses',
                        'snap_token' => $data->snap_token ?? $transaksi->snap_token,
                        'payment_type' => $paymentType ?? $transaksi->payment_type,
                        'total' => $grossAmount, // Update total jika ada perubahan
                    ]);
                    Log::info("Webhook: Transaksi {$orderId} diupdate dari pending ke LUNAS. Status Pembayaran: {$transaksi->status_pembayaran}, Status Transaksi: {$transaksi->status_transaksi}");
                    // Stok dan keranjang diasumsikan sudah diproses jika transaksi sudah ada.
                    // Jika Anda pernah membuat transaksi dengan status pending di awal dan mengurangi stok,
                    // maka logika pengurangan stok tidak boleh diulang di sini.
                }
            }
            // --- Akhir Logika Kreasi Transaksi & Pengurangan Stok ---

            // Logika untuk mengupdate status transaksi yang sudah ada menjadi gagal/pending
            elseif ($transaksi && $updateStatusOnly) {
                $statusPembayaranFinal = '';
                $statusTransaksiFinal = '';

                if ($transactionStatus == 'pending') {
                    $statusPembayaranFinal = 'pending';
                    $statusTransaksiFinal = 'diproses';
                } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                    $statusPembayaranFinal = 'gagal';
                    $statusTransaksiFinal = 'gagal';
                } elseif ($transactionStatus == 'capture' && $paymentType == 'credit_card' && $fraudStatus != 'accept') {
                    $statusPembayaranFinal = 'gagal';
                    $statusTransaksiFinal = 'gagal';
                }

                if (!empty($statusPembayaranFinal)) {
                    $transaksi->update([
                        'status_pembayaran' => $statusPembayaranFinal,
                        'status_transaksi' => $statusTransaksiFinal,
                        'snap_token' => $data->snap_token ?? $transaksi->snap_token,
                        'payment_type' => $paymentType ?? $transaksi->payment_type,
                    ]);
                    Log::info("Webhook: Transaksi {$orderId} diupdate. Status Pembayaran: {$transaksi->status_pembayaran}, Status Transaksi: {$transaksi->status_transaksi}");
                }
            }

            DB::commit();
            Log::info('Webhook: Transaksi database berhasil di-commit.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Webhook: Terjadi kesalahan saat memproses webhook (catch block): ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'order_id' => $orderId]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
