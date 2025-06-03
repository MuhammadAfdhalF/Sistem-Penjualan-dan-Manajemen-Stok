<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiOffline extends Model
{
    use HasFactory;

    protected $table = 'transaksi_offline';

    protected $fillable = [
        'kode_transaksi',
        'jenis_pelanggan',
        'tanggal',
        'total',
        'dibayar',
        'kembalian',
        'pelanggan_id',

    ];
    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function detail()
    {
        return $this->hasMany(TransaksiOfflineDetail::class, 'transaksi_id');
    }

    // TransaksiOffline.php
    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }
}
