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
            $table->integer('stok')->default(0);
            $table->string('satuan'); // contoh: pcs, bungkus, liter
            $table->decimal('harga_normal', 15, 2); // harga normal
            $table->decimal('harga_grosir', 15, 2)->nullable(); // harga grosir, bisa null jika tidak ada
            $table->string('gambar')->nullable(); // path ke gambar produk
            $table->string('kategori'); // contoh: makanan, minuman, rokok
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
