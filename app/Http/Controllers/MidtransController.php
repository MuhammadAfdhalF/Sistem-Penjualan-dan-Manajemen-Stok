<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Mungkin tidak perlu di sini, tapi tidak masalah jika ada
use Illuminate\Support\Str; // Digunakan untuk Str::contains
use App\Models\TransaksiOnline;
use App\Models\TransaksiOffline; // Import model TransaksiOffline
use Midtrans\Config;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\Stok;
use App\Models\TransaksiOnlineDetail;
use App\Models\TransaksiOfflineDetail; // Import model TransaksiOfflineDetail
use App\Models\Produk;
use App\Models\Satuan;
use App\Models\HargaProduk; // Mungkin tidak perlu di sini
use Illuminate\Support\Facades\DB;
use App\Models\User; // Mungkin tidak perlu di sini
use App\Models\PaymentLog;
use App\Models\Keuangan; // Import model Keuangan

class MidtransController extends Controller
{
    public function sukses(Request $request)
    {
        // Ambil total transaksi dari query parameter 'amount'
        $lastTransactionAmount = $request->query('amount', 0);

        return view('mobile.midtrans_sukses', [
            'activeMenu' => null,
            'last_transaction_amount' => $lastTransactionAmount // Teruskan ke view
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
        $snapToken = \Midtrans\Snap::getSnapToken($params); // Gunakan \Midtrans\Snap karena sudah di-import di atas

        return response()->json([
            'token' => $snapToken,
            'order_id' => $orderId
        ]);
    }


    public function handleWebhook(Request $request)
    {
        Log::info('Webhook Midtrans diterima.');
        Log::info('Raw Webhook Payload: ' . $request->getContent());

        // 1. Konfigurasi Midtrans untuk verifikasi notifikasi
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false) === 'true';
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // 2. Dapatkan notifikasi dari Midtrans
        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            Log::error('Webhook: Gagal menginisialisasi Midtrans Notification: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to initialize Midtrans Notification'], 500);
        }

        $transactionStatus = $notif->transaction_status;
        $orderId = $notif->order_id;
        $fraudStatus = $notif->fraud_status;
        $grossAmount = $notif->gross_amount;
        $paymentType = $notif->payment_type;
        $statusCode = $notif->status_code;

        Log::info("Webhook data: OrderID={$orderId}, Status={$transactionStatus}, FraudStatus={$fraudStatus}, PaymentType={$paymentType}, GrossAmount={$grossAmount}, StatusCode={$statusCode}");

        // 3. Verifikasi Signature Key (SANGAT PENTING UNTUK KEAMANAN)
        if (!$orderId || !$transactionStatus) {
            Log::warning('Webhook: OrderID atau TransactionStatus kosong setelah inisialisasi Notification.', ['data' => $notif]);
            return response()->json(['status' => 'invalid'], 400);
        }

        DB::beginTransaction();
        try {
            $transaksi = null;
            $transaksiDetails = collect(); // Initialize as empty collection
            $transaksiType = 'online'; // Default type

            // --- BAGIAN BARU: Tentukan jenis transaksi (online/offline) dari custom_field1 ---
            $customFields = json_decode($notif->custom_field1 ?? '{}', true);
            if (isset($customFields['transaksi_type'])) {
                $transaksiType = $customFields['transaksi_type'];
            }

            $localTransaksiId = $customFields['transaksi_id_local'] ?? null; // ID transaksi lokal dari custom_field

            if ($transaksiType === 'online') {
                $transaksi = TransaksiOnline::where('kode_transaksi', $orderId)->first();
                // Jika tidak ditemukan berdasarkan orderId, coba cari berdasarkan localTransaksiId jika ada
                if (!$transaksi && $localTransaksiId) {
                    $transaksi = TransaksiOnline::find($localTransaksiId);
                }
            } elseif ($transaksiType === 'offline') {
                $transaksi = TransaksiOffline::where('kode_transaksi', $orderId)->first();
                // Jika tidak ditemukan berdasarkan orderId, coba cari berdasarkan localTransaksiId jika ada
                if (!$transaksi && $localTransaksiId) {
                    $transaksi = TransaksiOffline::find($localTransaksiId);
                }
            }
            // --- AKHIR BAGIAN BARU ---

            // Jika transaksi tidak ditemukan, log error dan keluar
            if (!$transaksi) {
                Log::error('Webhook: Transaksi tidak ditemukan di database dengan OrderID: ' . $orderId . ' dan tipe: ' . $transaksiType);
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Transaction not found in local DB'], 404);
            }

            // Jika transaksi sudah lunas, langsung return OK untuk idempotency
            if ($transaksi->status_pembayaran === 'lunas') {
                Log::info('Webhook: Transaksi sudah lunas, tidak perlu update lagi.', ['order_id' => $orderId, 'type' => $transaksiType]);
                DB::commit();
                return response()->json(['status' => 'already paid'], 200);
            }

            $statusPembayaranFinal = '';
            $statusTransaksiFinal = '';
            $processStockDeduction = false; // Flag untuk mengurangi stok
            $createKeuanganEntry = false; // Flag untuk membuat entri keuangan (hanya untuk offline)

            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'accept') {
                        $statusPembayaranFinal = 'lunas';
                        $statusTransaksiFinal = 'diproses';
                        $processStockDeduction = true;
                        $createKeuanganEntry = true; // Jika offline dan lunas
                    } else {
                        $statusPembayaranFinal = 'gagal';
                        $statusTransaksiFinal = 'gagal';
                    }
                }
            } elseif ($transactionStatus == 'settlement') {
                $statusPembayaranFinal = 'lunas';
                $statusTransaksiFinal = 'diproses';
                $processStockDeduction = true;
                $createKeuanganEntry = true; // Jika offline dan lunas
            } elseif ($transactionStatus == 'pending') {
                $statusPembayaranFinal = 'pending';
                $statusTransaksiFinal = 'diproses';
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                $statusPembayaranFinal = 'gagal';
                $statusTransaksiFinal = 'gagal';
            } else {
                Log::warning('Webhook: Status transaksi tidak dikenal atau tidak ditangani: ' . $transactionStatus, ['order_id' => $orderId, 'type' => $transaksiType]);
                DB::commit();
                return response()->json(['status' => 'unhandled_status'], 200);
            }

            // Update status transaksi di database
            // TransaksiOffline memiliki 'dibayar' dan 'kembalian' yang perlu diisi jika lunas
            $updateData = [
                'status_pembayaran' => $statusPembayaranFinal,
                'snap_token' => $notif->snap_token ?? $transaksi->snap_token,
                'payment_type' => $paymentType,
                'total' => $grossAmount, // Pastikan total sesuai dengan Midtrans
            ];

            // Hanya update status_transaksi jika itu transaksi online atau jika statusnya berubah ke gagal
            // Transaksi offline mungkin memiliki alur status_transaksi yang berbeda
            if ($transaksiType === 'online' || $statusTransaksiFinal === 'gagal') {
                $updateData['status_transaksi'] = $statusTransaksiFinal;
            }

            // Jika transaksi offline dan lunas, isi 'dibayar' dengan total dan 'kembalian' dengan 0
            if ($transaksiType === 'offline' && $statusPembayaranFinal === 'lunas') {
                $updateData['dibayar'] = $grossAmount;
                $updateData['kembalian'] = 0;
            }

            $transaksi->update($updateData);
            Log::info("Webhook: Transaksi {$orderId} ({$transaksiType}) diupdate. Status Pembayaran: {$transaksi->status_pembayaran}, Status Transaksi: {$transaksi->status_transaksi}");

            // --- Logika Pengurangan Stok (HANYA JIKA STATUS LUNAS DAN BELUM DIKURANGI) ---
            if ($processStockDeduction) {
                // Ambil detail produk dari database lokal sesuai jenis transaksi
                if ($transaksiType === 'online') {
                    $transaksiDetails = TransaksiOnlineDetail::where('transaksi_id', $transaksi->id)->get();
                } elseif ($transaksiType === 'offline') {
                    $transaksiDetails = TransaksiOfflineDetail::where('transaksi_id', $transaksi->id)->get();
                } else {
                    $transaksiDetails = collect(); // Fallback
                }

                foreach ($transaksiDetails as $detail) {
                    $produk = Produk::with('satuans')->find($detail->produk_id);
                    if (!$produk) {
                        Log::error("Webhook: Produk dengan ID {$detail->produk_id} tidak ditemukan saat memproses detail transaksi {$orderId} ({$transaksiType}).");
                        continue;
                    }

                    $jumlahArr = $detail->jumlah_json;
                    // Pastikan jumlahArr adalah array sebelum iterasi
                    if (!is_array($jumlahArr)) {
                        Log::warning("Webhook: jumlah_json is not an array for detail ID: {$detail->id}. Skipping stock deduction for this detail.");
                        continue;
                    }

                    foreach ($jumlahArr as $satuanId => $qty) {
                        $qty = floatval($qty);
                        if ($qty <= 0) continue;

                        $satuan = Satuan::find($satuanId);
                        $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                        $jumlahUtama = $qty * $konversi;

                        // Cek stok kembali sebelum decrement
                        if ($produk->stok < $jumlahUtama) {
                            Log::error("Webhook: Stok tidak cukup saat mencoba mengurangi stok untuk transaksi {$orderId} ({$transaksiType}). Produk: {$produk->nama_produk}. Stok tersedia: {$produk->stok}, Diminta: {$jumlahUtama}. Transaksi akan tetap dianggap lunas, tapi stok tidak akurat.");
                        } else {
                            $produk->decrement('stok', $jumlahUtama);
                            Log::info("Webhook: Stok produk '{$produk->nama_produk}' dikurangi sebanyak {$jumlahUtama} unit utama untuk transaksi {$transaksiType}.");

                            Stok::create([
                                'produk_id' => $produk->id,
                                'satuan_id' => $satuanId,
                                'jenis' => 'keluar',
                                'jumlah' => $jumlahUtama,
                                'keterangan' => 'Transaksi ' . $transaksiType . ' via Midtrans #' . $orderId,
                            ]);
                            Log::info("Webhook: Catatan stok keluar untuk produk '{$produk->nama_produk}' berhasil dibuat untuk transaksi {$transaksiType}.");
                        }
                    }
                }
            }

            // --- BAGIAN BARU: Catatan Keuangan untuk Transaksi Offline yang Lunas ---
            if ($transaksiType === 'offline' && $createKeuanganEntry) {
                Keuangan::updateOrCreate(
                    [
                        'transaksi_id' => $transaksi->id, // Gunakan transaksi_id (offline)
                        'transaksi_online_id' => null, // Pastikan ini null untuk transaksi offline
                    ],
                    [
                        'tanggal' => $transaksi->tanggal,
                        'jenis' => 'pemasukan',
                        'nominal' => $transaksi->total,
                        'keterangan' => 'Pemasukan dari transaksi offline via Midtrans #' . $transaksi->kode_transaksi,
                        'sumber' => 'offline_midtrans', // Sumber baru untuk membedakan
                    ]
                );
                Log::info('Webhook: Catatan keuangan untuk transaksi offline via Midtrans berhasil dibuat/diupdate.');
            }
            // --- AKHIR BAGIAN BARU ---

            // Update PaymentLog (tetap sama untuk kedua jenis transaksi)
            PaymentLog::updateOrCreate(
                [
                    'transaksi_id' => ($transaksiType === 'online') ? $transaksi->id : null, // ID TransaksiOnline
                    'transaksi_offline_id' => ($transaksiType === 'offline') ? $transaksi->id : null, // ID TransaksiOffline
                    'gateway' => 'midtrans',
                    'external_id' => $notif->transaction_id ?? null,
                ],
                [
                    'metode' => $paymentType,
                    'status' => $transactionStatus,
                    'nominal' => $grossAmount,
                    'response_payload' => json_encode($notif->getResponse()),
                ]
            );
            Log::info('Webhook: PaymentLog berhasil diupdate/dibuat untuk transaksi: ' . $transaksi->kode_transaksi . ' (Tipe: ' . $transaksiType . ')');

            DB::commit();
            Log::info('Webhook: Transaksi database berhasil di-commit.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Webhook: Terjadi kesalahan saat memproses webhook (catch block): ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'order_id' => $orderId, 'type' => $transaksiType]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
