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

    protected $casts = [
        'jumlah' => 'integer',
        'harga_normal' => 'float',
        'harga_grosir' => 'float',
        'subtotal' => 'float',
    ];

    /**
     * Relasi ke transaksi offline (parent)
     */
    public function transaksi()
    {
        return $this->belongsTo(TransaksiOffline::class, 'transaksi_id');
    }

    /**
     * Relasi ke produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Mendapatkan total harga per item dengan format satuan bertingkat (opsional)
     */
    public function getJumlahBertingkatAttribute()
    {
        return $this->produk?->tampilkanStok3Tingkatan($this->jumlah);
    }
}
