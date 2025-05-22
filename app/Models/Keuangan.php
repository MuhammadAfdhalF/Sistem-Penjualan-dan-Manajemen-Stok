<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    use HasFactory;

    protected $table = 'keuangans';

    protected $fillable = [
        'transaksi_id',  // nullable, bisa manual
        'tanggal',
        'jenis',         // 'pemasukan' atau 'pengeluaran'
        'nominal',
        'keterangan',
        'sumber',        // contoh: 'offline', 'manual', 'online'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'float',
    ];

    /**
     * Relasi ke transaksi offline (jika ada)
     */
    public function transaksi()
    {
        return $this->belongsTo(TransaksiOffline::class, 'transaksi_id');
    }

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
        return $this->belongsTo(\App\Models\TransaksiOnline::class, 'transaksi_id');
    }
}
