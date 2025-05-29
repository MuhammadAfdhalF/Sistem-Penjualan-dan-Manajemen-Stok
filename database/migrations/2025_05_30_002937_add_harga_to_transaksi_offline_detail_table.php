<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHargaToTransaksiOfflineDetailTable extends Migration
{
    public function up()
    {
        Schema::table('transaksi_offline_detail', function (Blueprint $table) {
            $table->decimal('harga', 15, 2)->after('jumlah_json')->default(0);
        });
    }

    public function down()
    {
        Schema::table('transaksi_offline_detail', function (Blueprint $table) {
            $table->dropColumn('harga');
        });
    }
}
