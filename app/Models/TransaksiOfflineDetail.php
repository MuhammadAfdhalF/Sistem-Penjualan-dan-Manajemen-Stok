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
        'jumlah_json',
        'harga_json',  // gunakan ini untuk harga per satuan
        'subtotal',
    ];

    protected $casts = [
        'jumlah_json' => 'array',
        'harga_json' => 'array', // harga per satuan sebagai array
        'subtotal' => 'float',
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiOffline::class, 'transaksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function getJumlahBertingkatAttribute()
    {
        if (!is_array($this->jumlah_json) || !$this->produk || !$this->produk->satuans) {
            return 0;
        }

        $total = 0;

        foreach ($this->jumlah_json as $satuanId => $qty) {
            $satuanId = (int) $satuanId;
            $konversi = $this->produk->satuans->firstWhere('id', $satuanId)?->konversi_ke_satuan_utama ?? 1;
            $total += floatval($qty) * $konversi;
        }

        return $total;
    }
}
