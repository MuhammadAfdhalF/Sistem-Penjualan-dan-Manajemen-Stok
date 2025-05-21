<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $produk_id
 * @property int $satuan_id
 * @property string $jenis_pelanggan
 * @property float $harga
 */
class HargaProduk extends Model
{
    use HasFactory;

    protected $table = 'harga_produks';

    protected $fillable = [
        'produk_id',
        'satuan_id',
        'jenis_pelanggan',
        'harga',
    ];

    protected $casts = [
        'harga' => 'float',
    ];

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
}
