<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $produk_id
 * @property string $nama_satuan
 * @property int $level
 * @property float $konversi_ke_satuan_utama
 */
class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuans';

    protected $fillable = [
        'produk_id',
        'nama_satuan',
        'level',
        'konversi_ke_satuan_utama',
    ];

    protected $casts = [
        'level' => 'integer',
        'konversi_ke_satuan_utama' => 'float',
    ];

    /**
     * Relasi ke produk (setiap satuan milik satu produk)
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function hargaProduk()
    {
        return $this->hasMany(\App\Models\HargaProduk::class, 'satuan_id');
    }
}
