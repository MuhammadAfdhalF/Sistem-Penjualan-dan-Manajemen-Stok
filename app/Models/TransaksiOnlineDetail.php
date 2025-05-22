<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiOnlineDetail extends Model
{
    use HasFactory;

    protected $table = 'transaksi_online_detail';

    protected $fillable = [
        'transaksi_id',
        'produk_id',
        'satuan_id',
        'harga_id',
        'jumlah',
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'jumlah' => 'float',
        'harga' => 'float',
        'subtotal' => 'float',
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiOnline::class, 'transaksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }

    public function hargaProduk()
    {
        return $this->belongsTo(HargaProduk::class, 'harga_id');
    }
}
