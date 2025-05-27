<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keranjangs', function (Blueprint $table) {
            // Tambahkan field jumlah_json (text, nullable, setelah produk_id)
            $table->text('jumlah_json')->nullable()->after('produk_id');

            // Hapus foreign key satuan_id (pastikan sudah ada!)
            if (Schema::hasColumn('keranjangs', 'satuan_id')) {
                $table->dropForeign(['satuan_id']);
                $table->dropColumn('satuan_id');
            }

            // Hapus kolom jumlah (pastikan sudah ada!)
            if (Schema::hasColumn('keranjangs', 'jumlah')) {
                $table->dropColumn('jumlah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('keranjangs', function (Blueprint $table) {
            // Balikin field yang dihapus
            $table->foreignId('satuan_id')->nullable()->constrained('satuans')->nullOnDelete();
            $table->decimal('jumlah', 15, 2)->nullable();
            // Hapus jumlah_json
            $table->dropColumn('jumlah_json');
        });
    }
};
