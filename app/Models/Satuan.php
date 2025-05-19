<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $produk_id
 * @property string $nama_satuan
 * @property float $konversi_ke_satuan_utama
 */
class Satuan extends Model
{
    use HasFactory;

    protected $table = 'satuans';

    protected $fillable = [
        'produk_id',
        'nama_satuan',
        'konversi_ke_satuan_utama',
    ];

    protected $casts = [
        'konversi_ke_satuan_utama' => 'float',
    ];

    /**
     * Relasi ke Produk
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
