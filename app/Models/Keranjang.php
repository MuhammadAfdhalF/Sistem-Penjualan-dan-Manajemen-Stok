<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjangs';

    protected $fillable = [
        'user_id',
        'produk_id',
        'jumlah_json',
    ];

    protected $casts = [
        'jumlah_json' => 'array', // Otomatis decode/encode JSON saat akses lewat Eloquent!
    ];

    // RELASI
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    // ------ UTILITY METHODS UNTUK JUMLAH ------
    /**
     * Ambil jumlah total (akumulasi semua satuan)
     * Return float
     */
    public function totalJumlah()
    {
        // asumsikan value-nya array satuan_id => jumlah
        return collect($this->jumlah_json)->sum();
    }

    /**
     * Ambil jumlah per satuan dalam bentuk array [satuan_id => jumlah]
     */
    public function daftarJumlah()
    {
        return $this->jumlah_json ?? [];
    }
}
