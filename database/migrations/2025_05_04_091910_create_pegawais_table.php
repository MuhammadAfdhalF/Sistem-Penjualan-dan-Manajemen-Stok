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
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string("nama_pegawai");
            $table->string("alamat");
            $table->integer("umur");
            $table->date("tanggal_lahir");
            $table->string("tempat_lahir");
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->string("foto");  // Diubah: menghapus ->nullable() agar foto wajib diisi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};