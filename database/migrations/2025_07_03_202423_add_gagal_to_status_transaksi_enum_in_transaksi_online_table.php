<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade untuk query mentah

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     * Menambahkan nilai 'gagal' ke ENUM status_transaksi.
     */
    public function up(): void
    {
        // Mengubah kolom status_transaksi untuk menambahkan nilai 'gagal'
        // Pastikan urutan nilai ENUM yang sudah ada tidak berubah, hanya ditambahkan 'gagal' di akhir.
        DB::statement("ALTER TABLE transaksi_online CHANGE COLUMN status_transaksi status_transaksi ENUM('diproses', 'diantar', 'diambil', 'selesai', 'gagal') NOT NULL DEFAULT 'diproses'");
    }

    /**
     * Balikkan migrasi.
     * Menghapus nilai 'gagal' dari ENUM status_transaksi.
     * PERHATIAN: Jika ada data dengan nilai 'gagal', ini akan menyebabkan error saat rollback.
     * Pastikan tidak ada data 'gagal' sebelum rollback, atau ubah dulu nilainya jika diperlukan.
     */
    public function down(): void
    {
        // Mengubah kembali kolom status_transaksi untuk menghapus nilai 'gagal'
        DB::statement("ALTER TABLE transaksi_online CHANGE COLUMN status_transaksi status_transaksi ENUM('diproses', 'diantar', 'diambil', 'selesai') NOT NULL DEFAULT 'diproses'");
    }
};
