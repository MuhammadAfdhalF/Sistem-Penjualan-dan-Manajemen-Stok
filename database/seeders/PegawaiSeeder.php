<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Pegawai::factory()->count(25)->create();
        
        // $faker = Faker::create('id_ID');

        // for ($i = 0; $i < 25; $i++) {
        //     DB::table('pegawais')->insert([
        //         'nama_pegawai'   => $faker->name(),
        //         'alamat'         => $faker->address(),
        //         'umur'           => $faker->numberBetween(18, 60),
        //         'tanggal_lahir'  => $faker->date(),
        //         'tempat_lahir'   => $faker->city(),
        //         'jenis_kelamin'  => $faker->randomElement(['laki-laki', 'perempuan']),
        //     ]);
        // }
    }
}
