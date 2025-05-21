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

    /**
     * Relasi ke transaksi offline (parent)
     */
    public function transaksi()
    {
        return $this->belongsTo(TransaksiOffline::class);
    }

    /**
     * Relasi ke produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Relasi ke satuan
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }

    /**
     * Relasi ke harga produk (data harga yang digunakan saat transaksi)
     */
    public function hargaProduk()
    {
        return $this->belongsTo(HargaProduk::class, 'harga_id');
    }

    /**
     * Mendapatkan total jumlah dalam format bertingkat (opsional)
     */
    public function getJumlahBertingkatAttribute()
    {
        return $this->produk?->tampilkanStok3Tingkatan($this->jumlah);
    }
}
