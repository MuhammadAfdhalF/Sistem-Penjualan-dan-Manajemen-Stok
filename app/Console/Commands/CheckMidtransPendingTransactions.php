<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TransaksiOnline;
use Midtrans\Config;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentLog;
use App\Models\Produk;
use App\Models\Satuan;
use App\Models\TransaksiOnlineDetail;
use Illuminate\Support\Str; // <--- PASTIKAN BARIS INI ADA DI FILE ANDA

class CheckMidtransPendingTransactions extends Command
{
    protected $signature = 'midtrans:check-pending';
    protected $description = 'Checks pending Midtrans transactions and updates their status.';

    public function handle()
    {
        $this->info('Starting Midtrans pending transactions check...');
        Log::info('Midtrans:check-pending command started.');

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false) === 'true';
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Ambil transaksi online yang statusnya 'pending' dan sudah dibuat lebih dari X menit yang lalu
        // UBAH INI KEMBALI KE DURASI REALISTIS SETELAH PENGUJIAN!
        $pendingTransactions = TransaksiOnline::where('status_pembayaran', 'pending')
                                            ->where('metode_pembayaran', 'payment_gateway')
                                            ->where('created_at', '<', now()->subMinutes(1)) // Pastikan ini 1 untuk testing
                                            ->get();

        $this->info(count($pendingTransactions) . ' pending transactions found to check.');
        Log::info('Found ' . count($pendingTransactions) . ' pending transactions for check.');

        foreach ($pendingTransactions as $transaksi) {
            DB::beginTransaction();
            try {
                $orderId = $transaksi->kode_transaksi;
                $this->info("Checking status for Order ID: {$orderId}");
                Log::info("Checking Midtrans status for Order ID: {$orderId}");

                $statusPembayaranFinal = $transaksi->status_pembayaran; // Default ke status saat ini
                $statusTransaksiFinal = $transaksi->status_transaksi;
                $paymentType = null; // Default null, akan diisi jika ada respons valid
                $grossAmount = $transaksi->total; // Gunakan total dari DB lokal sebagai default

                try {
                    $status = Transaction::status($orderId);
                    // Jika API berhasil merespons, update variabel
                    $transactionStatus = $status->transaction_status;
                    $grossAmount = $status->gross_amount;
                    $paymentType = $status->payment_type;
                    $fraudStatus = $status->fraud_status;

                    $logMessage = "Midtrans API response for {$orderId}: Status={$transactionStatus}, Fraud={$fraudStatus}, PaymentType={$paymentType}";
                    $this->info($logMessage);
                    Log::info($logMessage);

                    // Logika pembaruan status berdasarkan respons Midtrans
                    if ($transactionStatus == 'capture') {
                        if ($paymentType == 'credit_card') {
                            if ($fraudStatus == 'accept') {
                                $statusPembayaranFinal = 'lunas';
                                $statusTransaksiFinal = 'diproses';
                            } else {
                                $statusPembayaranFinal = 'gagal';
                                $statusTransaksiFinal = 'gagal';
                            }
                        }
                    } elseif ($transactionStatus == 'settlement') {
                        $statusPembayaranFinal = 'lunas';
                        $statusTransaksiFinal = 'diproses';
                    } elseif ($transactionStatus == 'pending') {
                        // Masih pending di Midtrans, biarkan status lokal pending
                        $statusPembayaranFinal = 'pending';
                        $statusTransaksiFinal = 'diproses';
                    } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                        $statusPembayaranFinal = 'gagal';
                        $statusTransaksiFinal = 'gagal';
                    }
                } catch (\Exception $apiException) {
                    // TANGANI ERROR DARI MIDTRANS API DI SINI
                    // Jika Midtrans API mengembalikan error (misalnya 404 Transaction doesn't exist)
                    // Kita anggap transaksi ini gagal/kadaluarsa
                    $apiErrorMessage = $apiException->getMessage();
                    Log::error("Midtrans API error for {$orderId}: " . $apiErrorMessage);
                    
                    // Periksa apakah error adalah 'Transaction doesn't exist' atau 404
                    if (Str::contains($apiErrorMessage, '404') || Str::contains($apiErrorMessage, "Transaction doesn't exist")) {
                        $statusPembayaranFinal = 'gagal'; // Atau 'expire'
                        $statusTransaksiFinal = 'gagal';
                        $this->info("Transaction {$orderId} marked as 'gagal' due to Midtrans API 404/doesn't exist.");
                        Log::info("Transaction {$orderId} marked as 'gagal' due to Midtrans API 404/doesn't exist.");
                    } else {
                        // Untuk error API lainnya, mungkin biarkan pending atau log untuk investigasi manual
                        $this->error("Unhandled Midtrans API error for {$orderId}: " . $apiErrorMessage);
                        Log::error("Unhandled Midtrans API error for {$orderId}: " . $apiErrorMessage);
                        DB::rollBack(); // Rollback hanya untuk error API yang tidak ditangani
                        continue; // Lanjutkan ke transaksi berikutnya
                    }
                }

                // Hanya update jika status berubah
                if ($transaksi->status_pembayaran !== $statusPembayaranFinal) {
                    $transaksi->update([
                        'status_pembayaran' => $statusPembayaranFinal,
                        'status_transaksi' => $statusTransaksiFinal,
                        'payment_type' => $paymentType ?? $transaksi->payment_type, // Gunakan yang baru atau yang lama
                        'total' => $grossAmount, // Pastikan total sesuai Midtrans
                    ]);
                    $this->info("Transaction {$orderId} updated to {$statusPembayaranFinal}.");
                    Log::info("Transaction {$orderId} updated to {$statusPembayaranFinal}.");

                    // Tambahkan atau update PaymentLog
                    PaymentLog::updateOrCreate(
                        [
                            'transaksi_id' => $transaksi->id,
                            'gateway' => 'midtrans',
                            'external_id' => $status->transaction_id ?? null, // Gunakan $status jika ada, atau null
                        ],
                        [
                            'metode' => $paymentType ?? 'unknown',
                            'status' => $transactionStatus ?? 'failed_api_check', // Status dari Midtrans API atau indikator gagal cek API
                            'nominal' => $grossAmount,
                            'response_payload' => isset($status) ? json_encode($status) : '{"error": "Transaction not found on Midtrans API"}', // Simpan seluruh respons status
                        ]
                    );
                    Log::info("PaymentLog updated for transaction {$orderId}.");

                    // Jika status berubah menjadi lunas, kurangi stok (jika belum dikurangi oleh webhook)
                    if ($statusPembayaranFinal === 'lunas') {
                        Log::warning("Attempting to deduct stock for {$orderId} via scheduled command. Ensure no double deduction if webhook also processed.");
                        $transaksiDetails = TransaksiOnlineDetail::where('transaksi_id', $transaksi->id)->get();
                        foreach ($transaksiDetails as $detail) {
                            $produk = Produk::with('satuans')->find($detail->produk_id);
                            if ($produk) {
                                $jumlahArr = $detail->jumlah_json;
                                foreach ($jumlahArr as $satuanId => $qty) {
                                    $qty = floatval($qty);
                                    if ($qty <= 0) continue;

                                    $satuan = Satuan::find($satuanId);
                                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                                    $jumlahUtama = $qty * $konversi;

                                    if ($produk->stok >= $jumlahUtama) {
                                        $produk->decrement('stok', $jumlahUtama);
                                        Stok::create([
                                            'produk_id' => $produk->id,
                                            'satuan_id' => $satuanId,
                                            'jenis' => 'keluar',
                                            'jumlah' => $jumlahUtama,
                                            'keterangan' => 'Transaksi online via Midtrans (reconciliation) #' . $orderId,
                                        ]);
                                        Log::info("Stock for product '{$produk->nama_produk}' reduced by {$jumlahUtama} main units via reconciliation.");
                                    } else {
                                        Log::error("Reconciliation: Insufficient stock for product {$produk->nama_produk} for transaction {$orderId}.");
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $this->info("Transaction {$orderId} status remains {$statusPembayaranFinal}. No update needed.");
                    Log::info("Transaction {$orderId} status remains {$statusPembayaranFinal}. No update needed.");
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error processing transaction {$orderId}: " . $e->getMessage());
                Log::error("Error processing transaction {$orderId} in command (outer catch): " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        }

        $this->info('Midtrans pending transactions check finished.');
        Log::info('Midtrans:check-pending command finished.');
        return Command::SUCCESS;
    }
}