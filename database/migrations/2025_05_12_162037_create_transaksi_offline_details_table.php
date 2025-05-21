<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_offline_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi_offline')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produks')->onDelete('restrict');
            $table->foreignId('satuan_id')->nullable()->constrained('satuans')->onDelete('set null');
            $table->foreignId('harga_id')->constrained('harga_produks')->onDelete('restrict');

            $table->decimal('jumlah', 15, 2); // mendukung penjualan pecahan
            $table->decimal('harga', 15, 2); // harga yang digunakan pada saat transaksi
            $table->decimal('subtotal', 15, 2); // jumlah * harga

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_offline_detail');
    }
};
