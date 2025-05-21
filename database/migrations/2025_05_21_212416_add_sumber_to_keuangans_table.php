<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keuangans', function (Blueprint $table) {
            $table->string('sumber')->default('manual')->after('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('keuangans', function (Blueprint $table) {
            $table->dropColumn('sumber');
        });
    }
};
