<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon; // <-- Tambahkan ini

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/produk'; // Sesuaikan sesuai alur aplikasi Anda

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nama'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'no_hp'           => ['required', 'string', 'max:20', 'unique:users'],
            'alamat'          => ['required', 'string'],
            // 'umur'            => ['required', 'integer', 'min:1', 'max:150'], // <-- Dihapus
            'tanggal_lahir'   => ['required', 'date', 'before_or_equal:' . Carbon::now()->subYears(1)->format('Y-m-d')], // <-- Ditambahkan
            'jenis_pelanggan' => ['required', 'in:Toko Kecil,Individu'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        try {
            return User::create([
                'nama'            => $data['nama'],
                'email'           => $data['email'],
                'no_hp'           => $data['no_hp'],
                'alamat'          => $data['alamat'],
                // 'umur'            => $data['umur'], // <-- Dihapus
                'tanggal_lahir'   => $data['tanggal_lahir'], // <-- Ditambahkan
                'jenis_pelanggan' => $data['jenis_pelanggan'],
                'role'            => 'pelanggan', // Default role untuk registrasi
                'password'        => Hash::make($data['password']),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal registrasi: ' . $e->getMessage());
            // Redirect kembali ke halaman registrasi dengan pesan error
            return redirect()->back()
                ->withInput()
                ->withErrors(['register_error' => 'Registrasi gagal. Silakan coba lagi.']);
        }
    }
}
