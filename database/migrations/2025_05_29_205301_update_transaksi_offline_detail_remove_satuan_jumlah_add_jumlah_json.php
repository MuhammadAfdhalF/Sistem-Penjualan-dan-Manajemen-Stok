<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTransaksiOfflineDetailRemoveSatuanJumlahAddJumlahJson extends Migration
{
    public function up()
    {
        Schema::table('transaksi_offline_detail', function (Blueprint $table) {
            // Hapus foreign key dulu sebelum drop kolom satuan_id
            $table->dropForeign(['satuan_id']);
            $table->dropColumn(['satuan_id', 'jumlah']);
            $table->text('jumlah_json')->nullable()->after('harga_id');
        });
    }

    public function down()
    {
        Schema::table('transaksi_offline_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('satuan_id')->nullable()->after('produk_id');
            $table->decimal('jumlah', 15, 2)->default(0)->after('harga_id');
            $table->dropColumn('jumlah_json');

            // Tambahkan foreign key satuan_id kembali
            $table->foreign('satuan_id')->references('id')->on('satuans')->onDelete('set null');
        });
    }
}
