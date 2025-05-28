<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    use HasFactory;

    protected $table = 'keuangans';

    protected $fillable = [
        'transaksi_id',          // untuk transaksi offline
        'transaksi_online_id',   // untuk transaksi online
        'tanggal',
        'jenis',
        'nominal',
        'keterangan',
        'sumber',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'float',
    ];



    /**
     * Scope untuk pemasukan saja
     */
    public function scopePemasukan($query)
    {
        return $query->where('jenis', 'pemasukan');
    }

    /**
     * Scope untuk pengeluaran saja
     */
    public function scopePengeluaran($query)
    {
        return $query->where('jenis', 'pengeluaran');
    }

    public function transaksiOffline()
    {
        return $this->belongsTo(\App\Models\TransaksiOffline::class, 'transaksi_id');
    }

    public function transaksiOnline()
    {
        return $this->belongsTo(\App\Models\TransaksiOnline::class, 'transaksi_online_id');
    }
}
