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
        'harga_id',
        'jumlah_json',   // ubah dari 'jumlah' jadi 'jumlah_json'
        'harga',
        'subtotal',
    ];

    protected $casts = [
        'jumlah_json' => 'array',  // cast JSON ke array otomatis
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
     * Relasi ke harga produk (data harga yang digunakan saat transaksi)
     */
    public function hargaProduk()
    {
        return $this->belongsTo(HargaProduk::class, 'harga_id');
    }

    /**
     * Mendapatkan total jumlah dalam format bertingkat (opsional)
     * Jumlah JSON akan dijumlahkan dan dikonversi ke stok utama.
     */
    public function getJumlahBertingkatAttribute()
    {
        if (!$this->jumlah_json || !is_array($this->jumlah_json)) {
            return 0;
        }
        // Jika ada fungsi tampilkanStok3Tingkatan pada produk, bisa panggil di sini
        $total = 0;
        foreach ($this->jumlah_json as $satuanId => $qty) {
            $konversi = $this->produk->satuans->firstWhere('id', $satuanId)?->konversi_ke_satuan_utama ?? 1;
            $total += $qty * $konversi;
        }
        return $total;
    }
}
