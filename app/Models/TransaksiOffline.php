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
        'tanggal',
        'total',
        'dibayar',
        'kembalian',
    ];

    public function detail()
    {
        return $this->hasMany(TransaksiOfflineDetail::class, 'transaksi_id');
    }
}
