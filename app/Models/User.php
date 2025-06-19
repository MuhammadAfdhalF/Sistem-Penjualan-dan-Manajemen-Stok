<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        'umur',
        'jenis_pelanggan',
        'role',
        'password',
        'foto_user',
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
    ];

    public function keranjangs()
    {
        return $this->hasMany(\App\Models\Keranjang::class, 'user_id');
    }
}
