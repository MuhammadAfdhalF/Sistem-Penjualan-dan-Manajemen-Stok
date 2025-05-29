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
        'harga_json',    // harga per satuan (JSON, nullable)
        'subtotal',      // total harga item (decimal, nullable)
    ];

    protected $casts = [
        'jumlah_json' => 'array',
        'harga_json' => 'array',
        'subtotal' => 'float',
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiOnline::class, 'transaksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Menghitung jumlah total satuan utama (bertumpuk) dari jumlah_json.
     * Bisa digunakan untuk laporan stok real.
     */
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
