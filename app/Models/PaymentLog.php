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

    public function transaksi()
    {
        return $this->belongsTo(TransaksiOnline::class, 'transaksi_id');
    }
}
