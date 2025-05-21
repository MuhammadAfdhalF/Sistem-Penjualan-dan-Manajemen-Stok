<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stoks', function (Blueprint $table) {
            $table->foreignId('satuan_id')->nullable()->constrained('satuans')->onDelete('set null')->after('produk_id');
        });
    }

    public function down(): void
    {
        Schema::table('stoks', function (Blueprint $table) {
            $table->dropForeign(['satuan_id']);
            $table->dropColumn('satuan_id');
        });
    }
};
