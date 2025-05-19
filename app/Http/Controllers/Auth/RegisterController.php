<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/produk';

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'no_hp' => ['required', 'string', 'max:20', 'unique:users'],
            'alamat' => ['required', 'string'],
            'umur' => ['required', 'integer', 'min:1', 'max:150'],
            'jenis_pelanggan' => ['required', 'in:Toko Kecil,Individu'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data)
    {
        try {
            return User::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'umur' => $data['umur'],
                'jenis_pelanggan' => $data['jenis_pelanggan'],
                'role' => 'pelanggan',
                'password' => Hash::make($data['password']),
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
