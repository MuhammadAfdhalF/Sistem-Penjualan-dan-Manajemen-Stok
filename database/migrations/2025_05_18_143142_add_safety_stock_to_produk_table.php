<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->integer('safety_stock')->default(0)->after('stok');
            $table->integer('lead_time')->default(0)->after('safety_stock');       // dalam hari
            $table->integer('daily_usage')->default(0)->after('lead_time');       // kebutuhan per hari
        });
    }

    public function down()
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['safety_stock', 'lead_time', 'daily_usage']);
        });
    }
};
