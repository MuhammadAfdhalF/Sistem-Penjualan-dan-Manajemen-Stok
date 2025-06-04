<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiOnline extends Model
{
    use HasFactory;

    protected $table = 'transaksi_online';

    protected $fillable = [
        'user_id',
        'kode_transaksi',
        'tanggal',
        'metode_pembayaran',  // Mengganti diambil_di_toko menjadi metode_pengambilan
        'status_pembayaran',
        'status_transaksi',
        'total',
        'catatan',
        'metode_pengambilan', // Menambahkan metode_pengambilan
        'alamat_pengambilan',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'metode_pengambilan' => 'string', // Ganti dengan string untuk enum
        'total' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasMany(TransaksiOnlineDetail::class, 'transaksi_id');
    }
}
