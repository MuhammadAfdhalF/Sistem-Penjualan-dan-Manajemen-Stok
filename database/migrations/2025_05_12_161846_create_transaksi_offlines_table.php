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
        Schema::create('transaksi_offline', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->dateTime('tanggal');
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('dibayar', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_offlines');
    }
};
