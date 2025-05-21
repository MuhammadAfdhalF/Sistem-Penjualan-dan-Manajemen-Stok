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

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }

    public function getJumlahBertingkatAttribute(): string
    {
        $jumlah = (int) $this->jumlah; // jumlah dalam satuan utama (integer)

        // Ambil satuan produk terkait (relasi produk->satuans)
        $satuans = $this->produk->satuans()->orderByDesc('konversi_ke_satuan_utama')->get();

        if ($satuans->isEmpty()) {
            return $jumlah . ' ' . $this->produk->satuan_utama;
        }

        $result = [];
        foreach ($satuans as $satuan) {
            if ($satuan->konversi_ke_satuan_utama <= 0) continue;

            $jumlah_satuan = intdiv($jumlah, $satuan->konversi_ke_satuan_utama);
            $jumlah = $jumlah % $satuan->konversi_ke_satuan_utama;

            if ($jumlah_satuan > 0) {
                $result[] = $jumlah_satuan . ' ' . $satuan->nama_satuan;
            }
        }

        if ($jumlah > 0) {
            $result[] = $jumlah . ' ' . $this->produk->satuan_utama;
        }

        if (empty($result)) {
            return '0 ' . $this->produk->satuan_utama;
        }

        return implode(' ', $result);
    }
}
