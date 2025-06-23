<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengganti kolom 'umur' dengan 'tanggal_lahir'.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Pertama, hapus kolom 'umur'
            $table->dropColumn('umur');

            // Kedua, tambahkan kolom 'tanggal_lahir'
            // Sesuaikan posisi 'after' jika Anda punya preferensi lain
            $table->date('tanggal_lahir')->nullable()->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     * Mengembalikan kolom 'umur' dan menghapus 'tanggal_lahir'.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Pertama, hapus kolom 'tanggal_lahir'
            $table->dropColumn('tanggal_lahir');

            // Kedua, kembalikan kolom 'umur' (sesuaikan tipe dan atribut jika perlu)
            $table->integer('umur')->nullable()->after('alamat'); // Mengembalikan ke tipe integer, nullable
        });
    }
};
