<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_online_detail', function (Blueprint $table) {
            // Tambahkan kolom jumlah_json
            $table->text('jumlah_json')->nullable()->after('produk_id');

            // (Opsional) Tambah subtotal jika mau subtotal per produk
            // $table->decimal('subtotal', 15, 2)->nullable()->after('jumlah_json');

            // Hapus kolom lama (harus drop foreign key dulu jika ada)
            if (Schema::hasColumn('transaksi_online_detail', 'satuan_id')) {
                $table->dropForeign(['satuan_id']);
                $table->dropColumn('satuan_id');
            }
            if (Schema::hasColumn('transaksi_online_detail', 'harga_id')) {
                $table->dropForeign(['harga_id']);
                $table->dropColumn('harga_id');
            }
            if (Schema::hasColumn('transaksi_online_detail', 'jumlah')) {
                $table->dropColumn('jumlah');
            }
            if (Schema::hasColumn('transaksi_online_detail', 'harga')) {
                $table->dropColumn('harga');
            }
            if (Schema::hasColumn('transaksi_online_detail', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_online_detail', function (Blueprint $table) {
            // Balikin kolom lama (restore)
            $table->unsignedBigInteger('satuan_id')->nullable()->after('produk_id');
            $table->unsignedBigInteger('harga_id')->nullable()->after('satuan_id');
            $table->decimal('jumlah', 15, 2)->nullable()->after('harga_id');
            $table->decimal('harga', 15, 2)->nullable()->after('jumlah');
            $table->decimal('subtotal', 15, 2)->nullable()->after('harga');

            // Hapus jumlah_json
            $table->dropColumn('jumlah_json');
        });
    }
};
