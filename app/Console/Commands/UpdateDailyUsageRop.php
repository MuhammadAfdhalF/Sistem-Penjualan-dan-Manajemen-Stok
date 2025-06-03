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
        $tanggalMulai = Carbon::now()->subDays($periodeHari)->startOfDay();

        $produkList = Produk::all();

        foreach ($produkList as $produk) {
            // Ambil penjualan harian offline selama 30 hari (array tanggal => qty dalam satuan utama)
            $penjualanOfflinePerHari = TransaksiOfflineDetail::whereHas('transaksi', function ($q) use ($tanggalMulai) {
                $q->where('tanggal', '>=', $tanggalMulai);
            })
                ->where('produk_id', $produk->id)
                ->get()
                ->groupBy(function ($item) {
                    return $item->transaksi->tanggal->format('Y-m-d');
                })
                ->map(function ($items) {
                    $total = 0;
                    foreach ($items as $detail) {
                        $jumlahArr = $detail->jumlah_json;
                        if (!is_array($jumlahArr)) continue;

                        foreach ($jumlahArr as $satuanId => $qty) {
                            $satuan = \App\Models\Satuan::find($satuanId);
                            $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                            $total += $qty * $konversi;
                        }
                    }
                    return $total;
                });

            // Ambil penjualan harian online selama 30 hari
            $penjualanOnlinePerHari = TransaksiOnlineDetail::whereHas('transaksi', function ($q) use ($tanggalMulai) {
                $q->where('tanggal', '>=', $tanggalMulai);
            })
                ->where('produk_id', $produk->id)
                ->get()
                ->groupBy(function ($item) {
                    return $item->transaksi->tanggal->format('Y-m-d');
                })
                ->map(function ($items) {
                    $total = 0;
                    foreach ($items as $detail) {
                        $jumlahArr = $detail->jumlah_json;
                        if (!is_array($jumlahArr)) continue;

                        foreach ($jumlahArr as $satuanId => $qty) {
                            $satuan = \App\Models\Satuan::find($satuanId);
                            $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                            $total += $qty * $konversi;
                        }
                    }
                    return $total;
                });

            // Gabungkan penjualan per hari offline + online
            $penjualanGabungan = [];

            // Buat array tanggal lengkap selama periode untuk memastikan semua hari dihitung (termasuk yg 0)
            for ($i = 0; $i < $periodeHari; $i++) {
                $tgl = $tanggalMulai->copy()->addDays($i)->format('Y-m-d');
                $offlineQty = $penjualanOfflinePerHari[$tgl] ?? 0;
                $onlineQty = $penjualanOnlinePerHari[$tgl] ?? 0;
                $penjualanGabungan[$tgl] = $offlineQty + $onlineQty;
            }

            $totalTerjual = array_sum($penjualanGabungan);
            $dailyUsage = $totalTerjual / $periodeHari;

            $maxDailySales = max($penjualanGabungan);
            $leadTime = $produk->lead_time; // diasumsikan lead_time sudah dalam satuan hari

            // Hitung safety stock berdasarkan rumus:
            // Safety Stock = (penjualan harian tertinggi - rata-rata penjualan harian) * lead time
            $safetyStock = max(0, ($maxDailySales - $dailyUsage) * $leadTime);

            // Update produk
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
