<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\TransaksiOnline;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;
use App\Models\Stok;
use App\Models\TransaksiOnlineDetail;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Satuan;
use App\Models\HargaProduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class MidtransController extends Controller
{
    public function sukses()
    {
        return view('mobile.midtrans_sukses', [
            'activeMenu' => null
        ]);
    }

    // Metode getSnapToken ini sekarang akan menerima orderId dan total yang sudah dibuat dari ProsesTransaksiController
    public function getSnapToken(Request $request)
    {
        // Validasi input dari frontend
        $request->validate([
            'total' => 'required|numeric|min:1',
            'metode_pembayaran' => 'required|string',
            'metode_pengambilan' => 'required|string',
            'alamat_pengambilan' => 'nullable|string',
            'catatan' => 'nullable|string',
            // Pastikan order_id juga dikirim dari frontend jika Anda menggunakan ini
            'order_id' => 'required|string',
        ]);

        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false) === 'true';
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = $request->order_id; // Ambil orderId dari request
        $totalFinal = $request->total; // Ambil total dari request

        // Data customer dan item untuk Midtrans Snap
        $customer = [
            'first_name' => Auth::user()->nama,
            'email' => Auth::user()->email,
            'phone' => Auth::user()->no_hp,
        ];

        // Item details ini harus mencerminkan total transaksi, bukan per produk lagi
        // Karena transaksi sudah dibuat di ProsesTransaksiController
        $items = [[
            'id' => $orderId,
            'price' => (int) $totalFinal,
            'quantity' => 1,
            'name' => 'Total Belanja KZ Family'
        ]];

        $custom_fields = [
            'user_id' => Auth::id(),
            'metode_pembayaran' => $request->metode_pembayaran,
            'metode_pengambilan' => $request->metode_pengambilan,
            'alamat_pengambilan' => $request->alamat_pengambilan,
            'catatan' => $request->catatan,
        ];

        // Persiapkan parameter Snap
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $totalFinal,
            ],
            'customer_details' => $customer,
            'item_details' => $items, // Menggunakan items ringkasan
            'callbacks' => [
                'finish' => route('mobile.pesanan.sukses'),
                'error' => route('mobile.home.index'),
                'pending' => route('mobile.home.index'),
            ],
            'custom_fields' => $custom_fields
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

        // Cari transaksi yang sudah ada di database Anda
        $transaksi = TransaksiOnline::where('kode_transaksi', $orderId)->first();

        if (!$transaksi) {
            Log::error('Webhook: Transaksi tidak ditemukan di database. Ini seharusnya sudah dibuat oleh ProsesTransaksiController.', ['order_id' => $orderId]);
            // Jika transaksi tidak ditemukan, ada masalah serius di alur pembuatan transaksi awal
            return response()->json(['status' => 'transaction not found'], 404);
        }

        // Pastikan transaksi belum lunas untuk menghindari double processing
        if ($transaksi->status_pembayaran === 'lunas') {
            Log::info('Webhook: Transaksi sudah lunas, tidak perlu update lagi.', ['order_id' => $orderId]);
            return response()->json(['status' => 'already paid'], 200);
        }

        DB::beginTransaction();
        try {
            $updateData = [];
            $processStockAndCart = false; // Flag untuk mengurangi stok dan membersihkan keranjang

            if ($transactionStatus == 'capture') {
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'accept') {
                        $updateData['status_pembayaran'] = 'lunas';
                        $updateData['status_transaksi'] = 'diproses';
                        $processStockAndCart = true;
                    } else {
                        $updateData['status_pembayaran'] = 'gagal';
                        $updateData['status_transaksi'] = 'gagal';
                    }
                }
            } elseif ($transactionStatus == 'settlement') {
                $updateData['status_pembayaran'] = 'lunas';
                $updateData['status_transaksi'] = 'diproses';
                $processStockAndCart = true;
            } elseif ($transactionStatus == 'pending') {
                $updateData['status_pembayaran'] = 'pending';
                $updateData['status_transaksi'] = 'diproses';
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                $updateData['status_pembayaran'] = 'gagal';
                $updateData['status_transaksi'] = 'gagal';
            }

            // Update data transaksi di database
            if (!empty($updateData)) {
                $transaksi->update($updateData);
                Log::info("Transaksi {$orderId} diupdate. Status Pembayaran: {$transaksi->status_pembayaran}, Status Transaksi: {$transaksi->status_transaksi}");
            }

            // Jika pembayaran lunas, lakukan pembaruan stok dan bersihkan keranjang
            if ($processStockAndCart) {
                // Logika pengurangan stok dan pembersihan keranjang sudah dilakukan di ProsesTransaksiController
                // saat transaksi awal dibuat.
                // Webhook hanya perlu memastikan status pembayaran diupdate.
                // Jika Anda ingin pengurangan stok juga terjadi di webhook, Anda perlu memindahkan logika itu ke sini.
                // Namun, untuk menghindari duplikasi dan potensi masalah, kita asumsikan sudah dikurangi di awal.
                // Jika Anda ingin mengosongkan keranjang di sini, pastikan itu belum dikosongkan sebelumnya.

                // Hapus session terkait keranjang/form cepat setelah pembayaran berhasil
                // Ini penting jika session belum dihapus di ProsesTransaksiController
                if (session()->has('form_cepat_data')) {
                    session()->forget('form_cepat_data');
                    Log::info('Webhook: Sesi form_cepat_data dibersihkan.');
                }
                if (session()->has('keranjang_ids')) {
                    $keranjangIds = session('keranjang_ids', []);
                    Keranjang::where('user_id', $transaksi->user_id)->whereIn('id', $keranjangIds)->delete();
                    session()->forget('keranjang_ids');
                    Log::info('Webhook: Keranjang pengguna dibersihkan.');
                }
                Artisan::call('produk:update-dailyusage-rop');
                Log::info('Webhook: Perintah Artisan produk:update-dailyusage-rop dijalankan.');
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
