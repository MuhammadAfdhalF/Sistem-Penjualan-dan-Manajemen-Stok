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
        Schema::create('stoks', function (Blueprint $table) {
            $table->id(); // Primary key

            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');

            // ✅ Diperbaiki: nullable + nullOnDelete()
            $table->foreignId('satuan_id')->nullable()->constrained('satuans')->nullOnDelete();

            $table->enum('jenis', ['masuk', 'keluar']);
            $table->decimal('jumlah', 15, 2); // jumlah stok bisa pecahan
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stoks');
    }
};
