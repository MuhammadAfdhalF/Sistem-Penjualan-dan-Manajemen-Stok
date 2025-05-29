<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransaksiOfflineDetailAddHargaJsonRemoveHarga extends Migration
{
    public function up()
    {
        Schema::table('transaksi_offline_detail', function (Blueprint $table) {
            // Hapus kolom harga
            $table->dropColumn('harga');

            // Tambah kolom harga_json untuk simpan harga per satuan
            $table->text('harga_json')->nullable()->after('jumlah_json');
        });
    }

    public function down()
    {
        Schema::table('transaksi_offline_detail', function (Blueprint $table) {
            // Balik perubahan: hapus harga_json
            $table->dropColumn('harga_json');

            // Tambah kolom harga lagi
            $table->decimal('harga', 15, 2)->default(0)->after('jumlah_json');
        });
    }
}
