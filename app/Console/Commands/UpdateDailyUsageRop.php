<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\TransaksiOfflineDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateDailyUsageRop extends Command
{
    protected $signature = 'produk:update-dailyusage-rop';
    protected $description = 'Update daily usage dan ROP produk berdasarkan transaksi 30 hari terakhir';

    public function handle()
    {
        $periodeHari = 30;
        $tanggalMulai = Carbon::now()->subDays($periodeHari);

        $produkList = Produk::all();

        foreach ($produkList as $produk) {
            // Hitung total produk terjual dalam 30 hari terakhir
            $jumlahTerjual = TransaksiOfflineDetail::whereHas('transaksi', function ($q) use ($tanggalMulai) {
                $q->where('tanggal', '>=', $tanggalMulai);
            })->where('produk_id', $produk->id)->sum('jumlah');

            // Hitung daily usage
            $dailyUsage = $jumlahTerjual / $periodeHari;

            // Hitung ROP
            $rop = ($produk->lead_time * $dailyUsage) + $produk->safety_stock;

            // Simpan ke database
            $produk->update([
                'daily_usage' => $dailyUsage,
                'rop' => $rop,
            ]);

            // Tampilkan ke console
            $this->info("Produk ID {$produk->id} updated: daily_usage = {$dailyUsage}, rop = {$rop}");

            // Tulis log untuk debug
            Log::info("UpdateDailyUsageRop | Produk ID: {$produk->id} | Jumlah Terjual: {$jumlahTerjual} | Lead Time: {$produk->lead_time} | Safety Stock: {$produk->safety_stock} | Daily Usage: {$dailyUsage} | ROP: {$rop}");
        }

        $this->info("Update daily usage dan ROP selesai.");
        return 0;
    }
}
