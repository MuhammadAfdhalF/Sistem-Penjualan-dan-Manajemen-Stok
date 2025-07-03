<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTransaksiOfflineAndPaymentLogsTable extends Migration
{
    public function up()
    {
        // Update transaksi_offline
        Schema::table('transaksi_offline', function (Blueprint $table) {
            // Cek apakah kolom metode_pembayaran sudah ada
            if (!Schema::hasColumn('transaksi_offline', 'metode_pembayaran')) {
                $table->string('metode_pembayaran')->after('jenis_pelanggan');
            }

            // Cek apakah kolom snap_token sudah ada
            if (!Schema::hasColumn('transaksi_offline', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('metode_pembayaran');
            }

            // Cek apakah kolom payment_type sudah ada
            if (!Schema::hasColumn('transaksi_offline', 'payment_type')) {
                $table->string('payment_type')->nullable()->after('snap_token');
            }

            // Cek apakah kolom status_pembayaran sudah ada
            if (!Schema::hasColumn('transaksi_offline', 'status_pembayaran')) {
                $table->enum('status_pembayaran', ['pending', 'lunas', 'gagal', 'expire'])->after('payment_type');
            }
        });

        // Tambah kolom baru transaksi_offline_id di payment_logs
        Schema::table('payment_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_logs', 'transaksi_offline_id')) {
                $table->unsignedBigInteger('transaksi_offline_id')->nullable()->after('transaksi_id');
            }
        });

        // Hapus foreign key lama (jika ada) dan drop kolom transaksi_id
        Schema::table('payment_logs', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $keys = $sm->listTableForeignKeys('payment_logs');
            foreach ($keys as $fk) {
                if ($fk->getLocalColumns() === ['transaksi_id']) {
                    $table->dropForeign($fk->getName());
                }
            }
        });

        // Update foreign key untuk transaksi_online_id (tetap ada) dan tambahkan transaksi_offline_id
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->foreign('transaksi_online_id')->references('id')->on('transaksi_online')->onDelete('cascade');
            $table->foreign('transaksi_offline_id')->references('id')->on('transaksi_offline')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Revert transaksi_offline
        Schema::table('transaksi_offline', function (Blueprint $table) {
            $table->dropColumn(['metode_pembayaran', 'snap_token', 'payment_type', 'status_pembayaran']);
        });

        // Hapus foreign key & kolom baru dari payment_logs
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropForeign(['transaksi_online_id']);
            $table->dropForeign(['transaksi_offline_id']);
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropColumn(['transaksi_offline_id']);
        });

        // Restore transaksi_id jika perlu
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('transaksi_id')->nullable()->after('id');
        });
    }
}
