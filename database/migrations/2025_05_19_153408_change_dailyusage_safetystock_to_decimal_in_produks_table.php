<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->decimal('safety_stock', 10, 2)->change();
            $table->decimal('daily_usage', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->integer('safety_stock')->change();
            $table->integer('daily_usage')->change();
        });
    }
};
