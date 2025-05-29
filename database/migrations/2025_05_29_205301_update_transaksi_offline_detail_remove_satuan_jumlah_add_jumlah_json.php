<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransaksiOfflineDetailRemoveSatuanJumlahAddJumlahJson extends Migration
{
    public function up()
    {
        Schema::table('transaksi_offline_detail', function (Blueprint $table) {
            // Drop foreign key constraint pada satuan_id sebelum drop kolom
            $table->dropForeign(['satuan_id']);

            // Drop kolom satuan_id dan jumlah
            $table->dropColumn(['satuan_id', 'jumlah']);

            // Tambah kolom jumlah_json setelah kolom harga_id
            $table->text('jumlah_json')->nullable()->after('harga_id');
        });
    }

    public function down()
    {
        Schema::table('transaksi_offline_detail', function (Blueprint $table) {
            // Tambah kembali kolom satuan_id dan jumlah dengan tipe dan posisi semula
            $table->unsignedBigInteger('satuan_id')->nullable()->after('produk_id');
            $table->decimal('jumlah', 15, 2)->default(0)->after('harga_id');

            // Drop kolom jumlah_json
            $table->dropColumn('jumlah_json');

            // Tambah foreign key constraint satuan_id kembali
            $table->foreign('satuan_id')->references('id')->on('satuans')->onDelete('set null');
        });
    }
}
