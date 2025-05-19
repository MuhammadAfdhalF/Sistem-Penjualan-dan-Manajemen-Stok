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
        Schema::create('satuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->string('nama_satuan'); // misal: karung, kg, bks, pcs
            $table->integer('level')->default(1); // 1=besar, 2=sedang, 3=kecil
            $table->decimal('konversi_ke_satuan_utama', 15, 4); // berapa satuan utama setara 1 unit satuan ini
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
