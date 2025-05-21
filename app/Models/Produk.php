<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_produk
 * @property string|null $deskripsi
 * @property float $stok
 * @property string $satuan_utama
 * @property float $harga_normal
 * @property float|null $harga_grosir
 * @property float|null $rop
 * @property float|null $safety_stock
 * @property int|null $lead_time
 * @property float|null $daily_usage
 * @property string|null $gambar
 * @property string|null $kategori
 */
class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';

    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'stok',
        'satuan_utama',  // pastikan konsisten dengan migration
        'harga_normal',
        'harga_grosir',
        'gambar',
        'kategori',
        'safety_stock',
        'lead_time',
        'daily_usage',
    ];

    protected $casts = [
        'stok' => 'float',
        'harga_normal' => 'float',
        'harga_grosir' => 'float',
        'rop' => 'float',
        'safety_stock' => 'float',
        'lead_time' => 'integer',
        'daily_usage' => 'float',
    ];

    /**
     * Cek apakah stok saat ini di bawah atau sama dengan ROP.
     * Jika ROP null, anggap stok aman (false).
     */
    public function isStokDiBawahROP(): bool
    {
        if (is_null($this->rop)) {
            return false;
        }

        return $this->stok <= $this->rop;
    }

    /**
     * Accessor untuk ROP yang dihitung secara otomatis
     * berdasarkan lead_time, daily_usage, dan safety_stock.
     *
     * @return float
     */
    public function getCalculatedRopAttribute(): float
    {
        $leadTime = $this->lead_time ?? 0;
        $dailyUsage = $this->daily_usage ?? 0;
        $safetyStock = $this->safety_stock ?? 0;

        return ($leadTime * $dailyUsage) + $safetyStock;
    }

    // Relasi jika nanti ingin menambah model kategori
    // public function kategori()
    // {
    //     return $this->belongsTo(Kategori::class, 'kategori', 'nama_kategori');
    // }

    public function satuans()
    {
        return $this->hasMany(Satuan::class);
    }

    // ntuk mengubah angka stok berapa pun ke format bertingkat, bisa dipakai untuk kebutuhan reorder min dll
    public function tampilkanStok3Tingkatan(int $stok): string
    {
        $satuans = $this->satuans()->orderByDesc('konversi_ke_satuan_utama')->get();

        if ($satuans->isEmpty()) {
            return $stok . ' ' . $this->satuan_utama;
        }

        $result = [];
        foreach ($satuans as $satuan) {
            if ($satuan->konversi_ke_satuan_utama <= 0) continue;

            $jumlah = intdiv($stok, $satuan->konversi_ke_satuan_utama);
            $stok = $stok % $satuan->konversi_ke_satuan_utama;

            if ($jumlah > 0) {
                $result[] = $jumlah . ' ' . $satuan->nama_satuan;
            }
        }

        if ($stok > 0) {
            $result[] = $stok . ' ' . $this->satuan_utama;
        }

        if (empty($result)) {
            return '0 ' . $this->satuan_utama;
        }

        return implode(' ', $result);
    }

//  stok produk saat ini
    public function getStokBertingkatAttribute(): string
    {
        $stok = (int) $this->stok; // stok total, integer supaya lebih mudah mod/div
        $satuans = $this->satuans()->orderByDesc('konversi_ke_satuan_utama')->get();

        if ($satuans->isEmpty()) {
            return $stok . ' ' . $this->satuan_utama;
        }

        $result = [];
        foreach ($satuans as $satuan) {
            if ($satuan->konversi_ke_satuan_utama <= 0) continue; // safety check

            $jumlah = intdiv($stok, $satuan->konversi_ke_satuan_utama);
            $stok = $stok % $satuan->konversi_ke_satuan_utama;

            if ($jumlah > 0) {
                $result[] = $jumlah . ' ' . $satuan->nama_satuan;
            }
        }

        // Jika masih ada stok tersisa (lebih kecil dari satuan terkecil)
        if ($stok > 0) {
            // Asumsikan satuan terkecil = satuan_utama
            $result[] = $stok . ' ' . $this->satuan_utama;
        }

        if (empty($result)) {
            return '0 ' . $this->satuan_utama;
        }

        return implode(' ', $result); // contoh output: "10 slof 3 bks"
    }


    public function getRopAttribute()
    {
        return ($this->lead_time * $this->daily_usage) + $this->safety_stock;
    }
}
