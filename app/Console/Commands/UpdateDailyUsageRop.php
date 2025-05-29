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
        $tanggalMulai = Carbon::now()->subDays($periodeHari);

        $produkList = Produk::all();

        foreach ($produkList as $produk) {
            // Hitung total produk terjual dari transaksi offline
            $jumlahOffline = TransaksiOfflineDetail::whereHas('transaksi', function ($q) use ($tanggalMulai) {
                $q->where('tanggal', '>=', $tanggalMulai);
            })->where('produk_id', $produk->id)->get()->reduce(function ($carry, $detail) {
                // $detail->jumlah_json sudah berupa array karena cast di model, jadi gunakan langsung
                $jumlahArr = $detail->jumlah_json;
                if (!is_array($jumlahArr)) return $carry;

                $total = 0;
                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    $total += $qty * $konversi;
                }
                return $carry + $total;
            }, 0);


            // Hitung total produk terjual dari transaksi online
            $jumlahOnline = TransaksiOnlineDetail::whereHas('transaksi', function ($q) use ($tanggalMulai) {
                $q->where('tanggal', '>=', $tanggalMulai);
            })
                ->where('produk_id', $produk->id)
                ->get()
                ->reduce(function ($carry, $detail) {
                    // jumlah_json bisa array kosong/null, pastikan tetap 0 kalau kosong
                    return $carry + collect($detail->jumlah_json ?: [])->sum();
                }, 0);


            $jumlahTerjual = $jumlahOffline + $jumlahOnline;

            // Hitung daily usage
            $dailyUsage = $jumlahTerjual / $periodeHari;

            // Hitung ROP (Reorder Point)
            $rop = ($produk->lead_time * $dailyUsage) + $produk->safety_stock;

            // Simpan hanya daily_usage
            $produk->update([
                'daily_usage' => $dailyUsage,
            ]);

            // Log dan info ke console
            $this->info("Produk ID {$produk->id} updated: daily_usage = {$dailyUsage}, rop = {$rop}");

            Log::info("UpdateDailyUsageRop | Produk ID: {$produk->id} | Offline: {$jumlahOffline} | Online: {$jumlahOnline} | Lead Time: {$produk->lead_time} | Safety Stock: {$produk->safety_stock} | Daily Usage: {$dailyUsage} | ROP: {$rop}");
        }

        $this->info("Update daily usage selesai.");
        return 0;
    }
}
