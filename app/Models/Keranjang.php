<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjangs';

    protected $fillable = [
        'user_id',
        'produk_id',
        'satuan_id',
        'jumlah',
    ];

    protected $casts = [
        'jumlah' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
}
