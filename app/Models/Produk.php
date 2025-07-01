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
        // Menggunakan ROP yang dibulatkan ke atas untuk perbandingan yang lebih aman
        // ini penting karena tampilkanStok3Tingkatan juga pakai round/ceil
        return $this->stok <= ceil($this->rop);
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
     * * Menggunakan satuan dari relasi satuans() yang sudah didefinisikan.
     */
    public function tampilkanStok3Tingkatan(float|int $stok): string
    {
        // Bulatkan dan jadikan integer untuk memastikan perhitungan yang akurat
        $stokDalamSatuanUtama = (int) round($stok);

        // Asumsi relasi 'satuans' sudah di-eager load atau diakses dengan benar
        // Pastikan satuan diurutkan dari konversi terbesar ke terkecil
        $satuans = $this->satuans->sortByDesc('konversi_ke_satuan_utama');

        // Jika tidak ada satuan yang terdefinisi untuk produk ini
        if ($satuans->isEmpty()) {
            return (string) $stokDalamSatuanUtama;
        }

        $result = [];
        $remainingStok = $stokDalamSatuanUtama;
        $primaryUnitName = null;
        $hasAnyUnit = false; // Flag untuk mengecek apakah ada satuan yang terdefinisi

        // Cari nama satuan utama (konversi_ke_satuan_utama = 1)
        foreach ($satuans as $satuan) {
            if ($satuan->konversi_ke_satuan_utama > 0) { // Pastikan satuan valid
                $hasAnyUnit = true;
                if ($satuan->konversi_ke_satuan_utama == 1) {
                    $primaryUnitName = $satuan->nama_satuan;
                }
            }
        }

        // Jika tidak ada satuan valid sama sekali, kembalikan hanya angka
        if (!$hasAnyUnit) {
            return (string) $stokDalamSatuanUtama;
        }

        // Iterasi melalui satuan dari yang terbesar ke terkecil
        foreach ($satuans as $satuan) {
            if ($satuan->konversi_ke_satuan_utama <= 0) continue; // Lewati konversi yang tidak valid

            // Hindari memproses satuan utama di sini, akan ditangani terpisah untuk sisa
            // Ini untuk memastikan output "X satuan_besar Y satuan_utama" bukan "X satuan_besar 0 satuan_utama Y satuan_utama"
            if ($satuan->konversi_ke_satuan_utama == 1 && $primaryUnitName) {
                continue;
            }

            $jumlah = floor($remainingStok / $satuan->konversi_ke_satuan_utama);

            if ($jumlah > 0) {
                $result[] = $jumlah . ' ' . $satuan->nama_satuan;
                $remainingStok -= ($jumlah * $satuan->konversi_ke_satuan_utama);
            }
        }

        // Tangani sisa stok yang pasti dalam satuan utama
        if ($remainingStok > 0) {
            $result[] = $remainingStok . ' ' . ($primaryUnitName ?: 'unit'); // Default ke 'unit' jika nama satuan utama tidak ditemukan
        }
        // Jika stok awal 0, atau setelah konversi semua jadi 0, dan belum ada hasil yang ditambahkan
        elseif (empty($result) && $stokDalamSatuanUtama == 0) {
            return '0' . ($primaryUnitName ? ' ' . $primaryUnitName : '');
        }
        // Jika stok awal > 0 tapi lebih kecil dari semua satuan yang terdefinisi (misal: stok 5, tapi terkecil 10)
        elseif (empty($result) && $stokDalamSatuanUtama > 0) {
            return $stokDalamSatuanUtama . ' ' . ($primaryUnitName ?: 'unit');
        }

        return implode(' ', $result);
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
