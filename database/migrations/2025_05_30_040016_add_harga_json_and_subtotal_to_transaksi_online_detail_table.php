<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHargaJsonAndSubtotalToTransaksiOnlineDetailTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi_online_detail', function (Blueprint $table) {
            $table->text('harga_json')->nullable()->after('jumlah_json');
            $table->decimal('subtotal', 15, 2)->after('harga_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_online_detail', function (Blueprint $table) {
            $table->dropColumn('harga_json');
            $table->dropColumn('subtotal');
        });
    }
}
