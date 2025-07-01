<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\TransaksiOfflineDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\TransaksiOnlineDetail;

class UpdateDailyUsageRop extends Command
{
    protected $signature = 'produk:update-dailyusage-rop';
    protected $description = 'Update daily usage produk berdasarkan transaksi 30 hari terakhir';

    public function handle()
    {
        $periodeHari = 30;
        // Ubah baris ini: Ambil 30 hari terakhir TERMASUK hari ini.
        // Jadi, kita melihat 29 hari ke belakang dari hari ini.
        $tanggalMulai = Carbon::now()->subDays($periodeHari - 1)->startOfDay(); 

        $produkList = Produk::all();

        foreach ($produkList as $produk) {
            // --- DEBUGGING UNTUK PRODUK ID 1 SAJA (HAPUS SETELAH FIX) ---
            if ($produk->id == 1) { 
                Log::debug("Processing Produk ID: {$produk->id}");
                Log::debug("Tanggal Mulai Periode (Adjusted): {$tanggalMulai->format('Y-m-d H:i:s')}");
            }
            // --- AKHIR DEBUGGING ---

            $penjualanOfflinePerHari = TransaksiOfflineDetail::whereHas('transaksi', function ($q) use ($tanggalMulai) {
                $q->where('tanggal', '>=', $tanggalMulai);
            })
                ->where('produk_id', $produk->id)
                ->get()
                ->groupBy(function ($item) {
                    // --- DEBUGGING START ---
                    if ($item->produk_id == 1) Log::debug("  Offline Grouping Date: " . $item->transaksi->tanggal->format('Y-m-d'));
                    // --- DEBUGGING END ---
                    return $item->transaksi->tanggal->format('Y-m-d');
                })
                ->map(function ($items) {
                    $total = 0;
                    foreach ($items as $detail) {
                        $jumlahArr = $detail->jumlah_json;
                        if (!is_array($jumlahArr)) {
                            // --- DEBUGGING START ---
                            if ($detail->produk_id == 1) Log::warning("  Jumlah JSON is not an array for Offline Detail ID: {$detail->id}. Type: " . gettype($jumlahArr));
                            // --- DEBUGGING END ---
                            continue;
                        }
                        foreach ($jumlahArr as $satuanId => $qty) {
                            $satuan = \App\Models\Satuan::find($satuanId);
                            $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                            $total += $qty * $konversi;
                        }
                    }
                    return $total;
                });

            // --- DEBUGGING START ---
            if ($produk->id == 1) Log::debug("  Penjualan Offline Per Hari for Produk ID {$produk->id}: " . json_encode($penjualanOfflinePerHari->toArray()));
            // --- DEBUGGING END ---


            $penjualanOnlinePerHari = TransaksiOnlineDetail::whereHas('transaksi', function ($q) use ($tanggalMulai) {
                $q->where('tanggal', '>=', $tanggalMulai);
            })
                ->where('produk_id', $produk->id)
                ->get()
                ->groupBy(function ($item) {
                    // --- DEBUGGING START ---
                    if ($item->produk_id == 1) Log::debug("  Online Grouping Date: " . $item->transaksi->tanggal->format('Y-m-d'));
                    // --- DEBUGGING END ---
                    return $item->transaksi->tanggal->format('Y-m-d');
                })
                ->map(function ($items) {
                    $total = 0;
                    foreach ($items as $detail) {
                        $jumlahArr = $detail->jumlah_json;
                        if (!is_array($jumlahArr)) {
                            // --- DEBUGGING START ---
                            if ($detail->produk_id == 1) Log::warning("  Jumlah JSON is not an array for Online Detail ID: {$detail->id}. Type: " . gettype($jumlahArr));
                            // --- DEBUGGING END ---
                            continue;
                        }
                        foreach ($jumlahArr as $satuanId => $qty) {
                            $satuan = \App\Models\Satuan::find($satuanId);
                            $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                            $total += $qty * $konversi;
                        }
                    }
                    return $total;
                });
            
            // --- DEBUGGING START ---
            if ($produk->id == 1) Log::debug("  Penjualan Online Per Hari for Produk ID {$produk->id}: " . json_encode($penjualanOnlinePerHari->toArray()));
            // --- DEBUGGING END ---


            $penjualanGabungan = [];
            for ($i = 0; $i < $periodeHari; $i++) {
                $tgl = $tanggalMulai->copy()->addDays($i)->format('Y-m-d');
                $offlineQty = $penjualanOfflinePerHari[$tgl] ?? 0;
                $onlineQty = $penjualanOnlinePerHari[$tgl] ?? 0;
                $penjualanGabungan[$tgl] = $offlineQty + $onlineQty;
            }

            // // --- DD() DI SINI UNTUK MELIHAT HASIL LENGKAP (HAPUS SETELAH FIX) ---
            // if ($produk->id == 1) {
            //     dd([
            //         'produk_id' => $produk->id,
            //         'tanggal_mulai_periode_adjusted' => $tanggalMulai->format('Y-m-d H:i:s'),
            //         'penjualan_offline_per_hari_collection' => $penjualanOfflinePerHari->toArray(),
            //         'penjualan_online_per_hari_collection' => $penjualanOnlinePerHari->toArray(),
            //         'penjualan_gabungan_per_hari_generated_by_loop' => $penjualanGabungan,
            //         'total_terjual_akhir' => array_sum($penjualanGabungan),
            //     ]);
            // }
            // // --- AKHIR DD() ---

            $totalTerjual = array_sum($penjualanGabungan);
            $dailyUsage = $totalTerjual / $periodeHari;

            $maxDailySales = max($penjualanGabungan);
            $leadTime = $produk->lead_time ?? 0;

            $safetyStock = max(0, ($maxDailySales - $dailyUsage) * $leadTime);

            // --- DEBUGGING START ---
            if ($produk->id == 1) Log::debug("Produk ID {$produk->id} - Total Terjual: {$totalTerjual}, Daily Usage: {$dailyUsage}, Max Daily Sales: {$maxDailySales}, Lead Time: {$leadTime}, Safety Stock: {$safetyStock}");
            // --- DEBUGGING END ---

            $produk->update([
                'daily_usage' => $dailyUsage,
                'safety_stock' => $safetyStock,
            ]);

            $rop = ($leadTime * $dailyUsage) + $safetyStock;

            $this->info("Produk ID {$produk->id} updated: daily_usage = {$dailyUsage}, safety_stock = {$safetyStock}, rop = {$rop}");
            Log::info("UpdateDailyUsageRop | Produk ID: {$produk->id} | Daily Usage: {$dailyUsage} | Max Daily Sales: {$maxDailySales} | Lead Time: {$leadTime} | Safety Stock: {$safetyStock} | ROP: {$rop}");
        }

        $this->info("Update daily usage dan safety stock selesai.");
        return 0;
    }
}