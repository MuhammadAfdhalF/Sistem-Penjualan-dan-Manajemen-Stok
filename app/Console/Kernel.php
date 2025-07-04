<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CheckMidtransPendingTransactions; // Import command Anda
use App\Console\Commands\UpdateDailyUsageRop; // Import command produk:update-dailyusage-rop Anda

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Daftarkan semua command Anda di sini
        CheckMidtransPendingTransactions::class,
        UpdateDailyUsageRop::class, // Pastikan nama class ini sesuai dengan file command Anda
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Jadwal command produk:update-dailyusage-rop
        // setiap tgl 1
        $schedule->command('produk:update-dailyusage-rop')->monthlyOn(1, '00:01');

        // Jadwal command midtrans:check-pending
        // Jalankan command ini setiap menit
        $schedule->command('midtrans:check-pending')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
