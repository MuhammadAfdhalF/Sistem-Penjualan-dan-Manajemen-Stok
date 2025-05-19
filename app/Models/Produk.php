<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @property int $lead_time
 * @property float $daily_usage
 * @property float $safety_stock
 * @property int $stok
 */

class Produk extends Model
{
    use HasFactory;

    // Jika tabel memang 'produk', bisa dihapus properti ini
    protected $table = 'produks';

    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'stok',
        'satuan',
        'harga_normal',
        'harga_grosir',
        'gambar',
        'kategori',
        'rop',           // Kalau ROP manual
        'safety_stock',  // kalau pakai manual safety stock
        'lead_time',     // kalau pakai rumus
        'daily_usage',   // kalau pakai rumus
    ];

    // Casting tipe data agar selalu benar
    protected $casts = [
        'stok' => 'integer',
        'harga_normal' => 'integer',
        'harga_grosir' => 'integer',
        'rop' => 'integer',
        'safety_stock' => 'float',
        'lead_time' => 'integer',
        'daily_usage' => 'float',
    ];

    /**
     * Relasi: Satu produk memiliki banyak entri stok (jika ada tabel stok).
     */
    public function stoks()
    {
        return $this->hasMany(Stok::class);
    }

    /**
     * Cek apakah stok berada di bawah atau sama dengan ROP.
     * Bisa dipakai untuk logika peringatan.
     */
    public function isStokDiBawahROP()
    {
        return $this->stok <= $this->rop;
    }

    /**
     * (Optional) Hitung ROP otomatis jika ingin pakai rumus:
     * ROP = (Lead Time x Daily Usage) + Safety Stock
     */
    public function getCalculatedRopAttribute()
    {
        return ($this->lead_time * $this->daily_usage) + $this->safety_stock;
    }

    /**
     * (Opsional) Relasi ke model kategori jika digunakan
     */
    // public function kategori()
    // {
    //     return $this->belongsTo(Kategori::class);
    // }
}
