<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon; // <-- Pastikan ini ada

/**
 * @method bool update(array $attributes = [], array $options = [])
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang bisa diisi massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'alamat',
        // 'umur', // <-- Kolom 'umur' dihapus dari fillable karena akan dihapus dari DB
        'jenis_pelanggan',
        'role',
        'password',
        'foto_user',
        'tanggal_lahir', // <-- Tambahkan ini
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting data otomatis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'tanggal_lahir' => 'date', // <-- Penting: casting ke tipe date
    ];

    // Accessor untuk menghitung umur secara dinamis dari tanggal_lahir
    // Ketika Anda memanggil $user->umur, method ini akan dijalankan
    public function getUmurAttribute()
    {
        if (is_null($this->tanggal_lahir)) {
            return null; // Mengembalikan null jika tanggal_lahir kosong/null
        }
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    public function keranjangs()
    {
        return $this->hasMany(\App\Models\Keranjang::class, 'user_id');
    }
}
