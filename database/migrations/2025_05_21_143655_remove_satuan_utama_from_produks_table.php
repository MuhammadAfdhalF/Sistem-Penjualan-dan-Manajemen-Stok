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
        Schema::table('produks', function (Blueprint $table) {
            // Pastikan kolom ini ada sebelum dihapus
            if (Schema::hasColumn('produks', 'satuan_utama')) {
                $table->dropColumn('satuan_utama');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->string('satuan_utama')->after('stok'); // posisi bisa disesuaikan
        });
    }
};
