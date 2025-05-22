<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keuangans', function (Blueprint $table) {
            $table->foreignId('transaksi_online_id')
                ->nullable()
                ->after('transaksi_id')
                ->constrained('transaksi_online')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('keuangans', function (Blueprint $table) {
            $table->dropForeign(['transaksi_online_id']);
            $table->dropColumn('transaksi_online_id');
        });
    }
};
