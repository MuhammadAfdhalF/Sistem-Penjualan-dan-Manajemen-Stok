<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Pegawai extends Model
{
    use HasFactory;
    protected $quarded = ['id'];

    protected $fillable = [
        'nama_pegawai',
        'alamat',
        'umur',
        'tanggal_lahir',
        'tempat_lahir',
        'jenis_kelamin',
        'foto',
    ];
}
