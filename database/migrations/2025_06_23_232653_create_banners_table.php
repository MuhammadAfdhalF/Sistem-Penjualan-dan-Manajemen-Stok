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
        Schema::create('banners', function (Blueprint $table) {
            $table->id(); // Ini akan membuat kolom 'id' sebagai bigint auto increment primary key
            $table->string('nama_banner');
            $table->string('gambar_url');
            $table->integer('urutan')->default(0); // Default ke 0 jika tidak diset
            $table->boolean('is_aktif')->default(true); // Default aktif (true)
            $table->timestamps(); // Ini akan membuat kolom 'created_at' dan 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};