<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';

    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'stok',
        'satuan_besar',
        'konversi_satuan_besar_ke_utama',
        'gambar',
        'kategori',
        'safety_stock',
        'lead_time',
        'daily_usage',
    ];

    protected $casts = [
        'stok' => 'float',
        'konversi_satuan_besar_ke_utama' => 'float',
        'safety_stock' => 'float',
        'lead_time' => 'integer',
        'daily_usage' => 'float',
    ];

    // ============================
    // ===== RELATIONSHIPS ========
    // ============================

    public function satuans()
    {
        return $this->hasMany(Satuan::class);
    }

    public function hargaProduk()
    {
        return $this->hasOne(HargaProduk::class);
    }

    // ============================
    // ========== ROP =============
    // ============================

    public function getRopAttribute(): float
    {
        return ($this->lead_time * $this->daily_usage) + $this->safety_stock;
    }

    public function isStokDiBawahROP(): bool
    {
        return $this->stok <= $this->rop;
    }

    public function getCalculatedRopAttribute(): float
    {
        return $this->rop; // untuk alias atau akses via accessor `calculated_rop`
    }

    public function getCalculatedSafetyStockAttribute()
    {
        // Ambil data transaksi 30 hari terakhir dan hitung seperti rumus
        $periodeHari = 30;
        $tanggalMulai = now()->subDays($periodeHari)->startOfDay();

        // Ambil penjualan per hari (offline + online) dalam array tanggal => qty
        $penjualanPerHari = [];

        for ($i = 0; $i < $periodeHari; $i++) {
            $tgl = $tanggalMulai->copy()->addDays($i)->format('Y-m-d');

            $offlineQty = \App\Models\TransaksiOfflineDetail::whereHas('transaksi', function ($q) use ($tgl) {
                $q->whereDate('tanggal', $tgl);
            })
                ->where('produk_id', $this->id)
                ->get()
                ->sum(function ($detail) {
                    $total = 0;
                    $jumlahArr = $detail->jumlah_json;
                    if (is_array($jumlahArr)) {
                        foreach ($jumlahArr as $satuanId => $qty) {
                            $satuan = \App\Models\Satuan::find($satuanId);
                            $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                            $total += $qty * $konversi;
                        }
                    }
                    return $total;
                });

            $onlineQty = \App\Models\TransaksiOnlineDetail::whereHas('transaksi', function ($q) use ($tgl) {
                $q->whereDate('tanggal', $tgl);
            })
                ->where('produk_id', $this->id)
                ->get()
                ->sum(function ($detail) {
                    $total = 0;
                    $jumlahArr = $detail->jumlah_json;
                    if (is_array($jumlahArr)) {
                        foreach ($jumlahArr as $satuanId => $qty) {
                            $satuan = \App\Models\Satuan::find($satuanId);
                            $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                            $total += $qty * $konversi;
                        }
                    }
                    return $total;
                });

            $penjualanPerHari[$tgl] = $offlineQty + $onlineQty;
        }

        if (empty($penjualanPerHari)) return 0;

        $rataRata = array_sum($penjualanPerHari) / $periodeHari;
        $tertinggi = max($penjualanPerHari);
        $leadTime = $this->lead_time ?? 0;

        $safetyStock = max(0, ($tertinggi - $rataRata) * $leadTime);

        return round($safetyStock, 2);
    }


    // ============================
    // ==== STOK BERTINGKAT =======
    // ============================

    /**
     * Tampilkan stok dengan pecahan satuan berdasarkan level satuan.
     * 
     * Menggunakan satuan dari relasi satuans() yang sudah didefinisikan.
     */
    public function tampilkanStok3Tingkatan(float|int $stok): string
    {
        $stokInt = (int) $stok;
        $satuans = $this->satuans()->orderByDesc('konversi_ke_satuan_utama')->get();

        if ($satuans->isEmpty()) {
            // Kalau tidak ada satuan, cukup tampilkan stok saja
            return (string) $stokInt;
        }

        $result = [];
        foreach ($satuans as $satuan) {
            if ($satuan->konversi_ke_satuan_utama <= 0) continue;

            $jumlah = intdiv($stokInt, $satuan->konversi_ke_satuan_utama);
            $stokInt %= $satuan->konversi_ke_satuan_utama;

            if ($jumlah > 0) {
                $result[] = $jumlah . ' ' . $satuan->nama_satuan;
            }
        }

        // Jika sisa stok ada (lebih kecil dari satuan terkecil)
        if ($stokInt > 0) {
            $result[] = $stokInt; // Tidak ada satuan utama, hanya angka saja
        }

        return $result ? implode(' ', $result) : '0';
    }

    public function getStokBertingkatAttribute(): string
    {
        return $this->tampilkanStok3Tingkatan($this->stok);
    }

    // ============================
    // ====== HARGA PRODUK ========
    // ============================

    public function hargaProduks()
    {
        return $this->hasMany(HargaProduk::class);
    }

    public function hargaNormalAktif()
    {
        return $this->hargaProduks()
            ->where('tipe', 'normal')
            ->aktif()
            ->orderByDesc('tanggal_mulai')
            ->first();
    }

    public function hargaGrosirAktif()
    {
        return $this->hargaProduks()
            ->where('tipe', 'grosir')
            ->aktif()
            ->orderByDesc('tanggal_mulai')
            ->first();
    }
}
