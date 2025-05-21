<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    use HasFactory;

    protected $table = 'keuangans';

    protected $fillable = [
        'tanggal',
        'jenis',       // 'masuk' atau 'keluar'
        'nominal',
        'keterangan',
        'sumber',      // contoh: 'penjualan offline', 'pengeluaran operasional', dll
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'float',
    ];

    /**
     * Scope untuk filter transaksi masuk
     */
    public function scopeMasuk($query)
    {
        return $query->where('jenis', 'masuk');
    }

    /**
     * Scope untuk filter transaksi keluar
     */
    public function scopeKeluar($query)
    {
        return $query->where('jenis', 'keluar');
    }
}
