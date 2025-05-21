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
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk');
            $table->text('deskripsi')->nullable();

            // stok disimpan dalam satuan terkecil (decimal untuk pecahan stok)
            $table->decimal('stok', 15, 2)->default(0);

            // satuan terkecil (utama), misal: pcs, bungkus, kg
            $table->string('satuan_utama');

            // satuan besar default (misal: karton, slof, karung) opsional untuk tampilan
            $table->string('satuan_besar')->nullable();

            // konversi satuan besar ke satuan utama, misal: 1 karton = 40 pcs
            $table->decimal('konversi_satuan_besar_ke_utama', 15, 2)->nullable();

            $table->string('gambar')->nullable();
            $table->string('kategori')->index();

            // Untuk keperluan perhitungan stok otomatis
            $table->integer('lead_time')->default(0);
            $table->decimal('safety_stock', 10, 2)->default(0);
            $table->decimal('daily_usage', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
