<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTransaksiOfflineDetailRemoveSatuanJumlah extends Migration
{
    public function up()
    {
        $tableName = 'transaksi_offline_detail';

        // Drop foreign key satuan_id jika ada
        $foreignKeys = $this->listTableForeignKeys($tableName);
        if (in_array('transaksi_offline_detail_satuan_id_foreign', $foreignKeys)) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign('transaksi_offline_detail_satuan_id_foreign');
            });
        }

        // Drop kolom satuan_id jika ada
        if (Schema::hasColumn($tableName, 'satuan_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('satuan_id');
            });
        }

        // Drop kolom jumlah jika ada
        if (Schema::hasColumn($tableName, 'jumlah')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('jumlah');
            });
        }

        // Drop foreign key harga_id jika ada
        if (in_array('transaksi_offline_detail_harga_id_foreign', $foreignKeys)) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign('transaksi_offline_detail_harga_id_foreign');
            });
        }

        // Drop kolom harga_id jika ada
        if (Schema::hasColumn($tableName, 'harga_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('harga_id');
            });
        }

        // Drop kolom harga jika ada
        if (Schema::hasColumn($tableName, 'harga')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('harga');
            });
        }

        // Jumlah_json sudah ada, jadi tidak perlu tambah lagi di sini
    }

    public function down()
    {
        Schema::table('transaksi_offline_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('satuan_id')->nullable()->after('produk_id');
            $table->decimal('jumlah', 15, 2)->default(0)->after('harga_id');

            $table->unsignedBigInteger('harga_id')->nullable()->after('satuan_id');
            $table->decimal('harga', 15, 2)->default(0)->after('harga_id');

            $table->foreign('satuan_id')->references('id')->on('satuans')->onDelete('set null');
            $table->foreign('harga_id')->references('id')->on('harga_produks')->onDelete('cascade');
        });
    }

    /**
     * List foreign keys on a table (MySQL)
     */
    protected function listTableForeignKeys(string $table): array
    {
        $database = env('DB_DATABASE');
        $results = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME != 'PRIMARY'", [$database, $table]);
        return array_map(fn($r) => $r->CONSTRAINT_NAME, $results);
    }
}
