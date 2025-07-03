<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiOffline extends Model
{
    use HasFactory;

    protected $table = 'transaksi_offline';

    protected $fillable = [
        'kode_transaksi',
        'jenis_pelanggan',
        'tanggal',
        'total',
        'dibayar',
        'kembalian',
        'pelanggan_id',
        'metode_pembayaran',   // <-- TAMBAHKAN INI
        'snap_token',          // <-- TAMBAHKAN INI
        'payment_type',        // <-- TAMBAHKAN INI
        'status_pembayaran',   // <-- TAMBAHKAN INI
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'total' => 'float',
        'dibayar' => 'float',
        'kembalian' => 'float',
        // Tambahkan casting untuk kolom enum jika diperlukan, atau biarkan string
        'status_pembayaran' => 'string', // atau 'enum' jika Laravel versi baru mendukung
    ];

    public function detail()
    {
        return $this->hasMany(TransaksiOfflineDetail::class, 'transaksi_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }
}
