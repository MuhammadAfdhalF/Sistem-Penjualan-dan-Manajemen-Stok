<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi_online')->onDelete('cascade');
            $table->string('gateway'); // e.g. xendit, midtrans
            $table->string('external_id')->nullable(); // ID dari payment gateway
            $table->string('metode')->nullable(); // qris, e-wallet, VA, dll
            $table->string('status'); // pending, sukses, gagal
            $table->decimal('nominal', 15, 2);
            $table->json('response_payload')->nullable(); // optional untuk menyimpan response JSON
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
