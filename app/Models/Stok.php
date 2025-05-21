<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id',
        'jenis',        // 'masuk' atau 'keluar'
        'jumlah',
        'keterangan',
        'satuan_id',    // relasi ke satuan jika ada
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Relasi ke Produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Relasi ke Satuan (opsional, jika stok masuk/keluar dicatat berdasarkan satuan tertentu)
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }

    /**
     * Accessor untuk menampilkan jumlah stok dalam format bertingkat (karton, pcs, dll)
     *
     * @return string
     */
    public function getJumlahBertingkatAttribute(): string
    {
        $jumlah = (int) $this->jumlah;

        // Ambil daftar satuan dari produk, urut dari besar ke kecil
        $satuans = $this->produk->satuans()->orderByDesc('konversi_ke_satuan_utama')->get();

        if ($satuans->isEmpty()) {
            return $jumlah . ' satuan';
        }

        $result = [];
        foreach ($satuans as $satuan) {
            if ($satuan->konversi_ke_satuan_utama <= 0) continue;

            $jumlahSatuan = intdiv($jumlah, $satuan->konversi_ke_satuan_utama);
            $jumlah = $jumlah % $satuan->konversi_ke_satuan_utama;

            if ($jumlahSatuan > 0) {
                $result[] = $jumlahSatuan . ' ' . $satuan->nama_satuan;
            }
        }

        if ($jumlah > 0) {
            $result[] = $jumlah . ' satuan';
        }

        return empty($result)
            ? '0 satuan'
            : implode(' ', $result);
    }
}
