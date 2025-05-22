<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_online', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('kode_transaksi')->unique();
            $table->dateTime('tanggal');
            $table->enum('metode_pembayaran', ['payment_gateway', 'cod', 'bayar_di_toko']);
            $table->enum('status_pembayaran', ['pending', 'lunas', 'gagal']);
            $table->enum('status_transaksi', ['diproses', 'diantar', 'diambil', 'selesai', 'batal']);
            $table->decimal('total', 15, 2);
            $table->text('catatan')->nullable();
            $table->boolean('diambil_di_toko')->default(false);
            $table->text('alamat_pengambilan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_online');
    }
};
