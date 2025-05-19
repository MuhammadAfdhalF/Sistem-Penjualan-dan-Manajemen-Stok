<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;

    // Menentukan kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'produk_id',
        'jenis',  // Masuk atau Keluar
        'jumlah',
        'keterangan'
    ];

    /**
     * Relasi dengan model Produk.
     * Setiap stok milik satu produk.
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    // Konversi format tanggal (jika ada kolom terkait tanggal seperti created_at dan updated_at)
    protected $dates = ['created_at', 'updated_at'];

    // Validasi tambahan bisa ditambahkan di sini jika perlu
}
