<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nama' => 'admin',
                'no_hp' => '081234567890',
                'alamat' => 'Alamat Admin',
                'umur' => 30,
                'jenis_pelanggan' => 'Toko Kecil', // atau 'Individu' jika kamu ingin
                'role' => 'admin',
                'password' => Hash::make('admin12345678')
            ]
        );
    }
}
