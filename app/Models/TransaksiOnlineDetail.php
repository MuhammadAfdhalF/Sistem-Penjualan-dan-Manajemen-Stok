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
        'jumlah_json',
        // (opsional: 'subtotal')
    ];

    protected $casts = [
        'jumlah_json' => 'array', // auto decode/encode JSON
        // 'subtotal' => 'float', // jika tetap ingin subtotal total per produk
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiOnline::class, 'transaksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    // Helper: ambil jumlah total semua satuan
    public function totalJumlah()
    {
        return collect($this->jumlah_json)->sum();
    }

    // Helper: daftar jumlah per satuan
    public function daftarJumlah()
    {
        return $this->jumlah_json ?? [];
    }
}
