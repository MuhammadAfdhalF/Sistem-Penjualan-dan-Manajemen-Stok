<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $table = 'payment_logs';

    protected $fillable = [
        'transaksi_id',  
        'transaksi_offline_id', // <-- TAMBAHKAN INI
        'gateway',
        'external_id',
        'metode',
        'status',
        'nominal',
        'response_payload',
    ];

    protected $casts = [
        'nominal' => 'float',
        'response_payload' => 'array',
    ];

    // Karena payment_logs bisa berelasi dengan TransaksiOnline atau TransaksiOffline,
    // kita membuat dua relasi terpisah.
    // Anda akan memanggilnya sesuai kebutuhan (misal: $paymentLog->transaksiOnline atau $paymentLog->transaksiOffline).

    public function transaksiOnline()
    {
        return $this->belongsTo(TransaksiOnline::class, 'transaksi_online_id');
    }

    public function transaksiOffline()
    {
        return $this->belongsTo(TransaksiOffline::class, 'transaksi_offline_id');
    }

    // Anda bisa juga menambahkan accessor jika ingin satu method untuk mendapatkan objek transaksi,
    // tapi ini opsional dan bisa disesuaikan dengan kebutuhan Anda.
    // public function payable()
    // {
    //     if ($this->transaksi_online_id) {
    //         return $this->transaksiOnline;
    //     } elseif ($this->transaksi_offline_id) {
    //         return $this->transaksiOffline;
    //     }
    //     return null;
    // }
}
