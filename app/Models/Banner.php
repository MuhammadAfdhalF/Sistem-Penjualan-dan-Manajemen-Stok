<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'banners'; // Menghubungkan model ini dengan tabel 'banners'

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_banner',
        'gambar_url',
        'urutan',
        'is_aktif',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_aktif' => 'boolean', // Memastikan kolom is_aktif di-cast ke tipe boolean
        'urutan' => 'integer',   // Memastikan kolom urutan di-cast ke tipe integer
    ];
}
