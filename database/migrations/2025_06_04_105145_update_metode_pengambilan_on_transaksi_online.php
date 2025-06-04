<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaksi_online', function (Blueprint $table) {
            // Menghapus kolom diambil_di_toko
            $table->dropColumn('diambil_di_toko');

            // Menambahkan kolom metode_pengambilan
            $table->enum('metode_pengambilan', ['ambil di toko', 'diantar'])->default('diantar');
        });
    }

    public function down()
    {
        Schema::table('transaksi_online', function (Blueprint $table) {
            // Menghapus kolom metode_pengambilan
            $table->dropColumn('metode_pengambilan');

            // Menambahkan kolom diambil_di_toko
            $table->boolean('diambil_di_toko')->default(false);
        });
    }
};
