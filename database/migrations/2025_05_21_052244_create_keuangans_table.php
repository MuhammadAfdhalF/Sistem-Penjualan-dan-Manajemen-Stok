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
        Schema::create('keuangans', function (Blueprint $table) {
            $table->id();

            // Sumber transaksi (nullable karena mungkin bisa manual di luar transaksi offline)
            $table->foreignId('transaksi_id')
                ->nullable()
                ->constrained('transaksi_offline')
                ->onDelete('set null');

            // Jenis transaksi keuangan: pemasukan atau pengeluaran
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);

            // Nominal transaksi
            $table->decimal('nominal', 15, 2);

            // Keterangan tambahan
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangans');
    }
};
