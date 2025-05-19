<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiOfflineDetail extends Model
{
    use HasFactory;

    protected $table = 'transaksi_offline_detail';

    protected $fillable = [
        'transaksi_id',
        'produk_id',
        'jumlah',
        'harga_normal',
        'harga_grosir',
        'subtotal',
    ];


    public function transaksi()
    {
        return $this->belongsTo(TransaksiOffline::class, 'transaksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
