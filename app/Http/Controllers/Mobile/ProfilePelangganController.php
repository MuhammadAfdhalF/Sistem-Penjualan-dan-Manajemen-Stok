<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; 

class ProfilePelangganController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

        return view('mobile.profile_pelanggan', [
            'activeMenu' => 'profile',
            'jenisPelanggan' => $jenisPelanggan,
            'user' => $user, // ← ini wajib ada
        ]);
    }


    public function edit()
    {
        try {
            $user = Auth::user();
            $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

            return view('mobile.edit_profile_pelanggan', [
                'activeMenu' => 'profile',
                'jenisPelanggan' => $jenisPelanggan,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal membuka halaman edit profil: ' . $e->getMessage());
            return redirect()->route('mobile.profile_pelanggan.index')->with('error', 'Tidak dapat membuka halaman edit profil.');
        }
    }

    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'nama'            => 'required|string|max:255',
                'email'           => 'required|email|unique:users,email,' . $user->id,
                'no_hp'           => 'required|unique:users,no_hp,' . $user->id,
                'alamat'          => 'required|string',
                // 'umur'            => 'required|integer|min:1', // <-- Dihapus
                'tanggal_lahir'   => 'required|date|before_or_equal:' . Carbon::now()->subYears(1)->format('Y-m-d'), // <-- Ditambahkan
                'jenis_pelanggan' => 'required|in:Toko Kecil,Individu',
                'password'        => 'nullable|string|min:6|confirmed',
                'foto_user'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ], [
                'nama.required'            => 'Nama harus diisi.',
                'email.required'           => 'Email harus diisi.',
                'email.email'              => 'Format email tidak valid.',
                'email.unique'             => 'Email sudah digunakan.',
                'no_hp.required'           => 'Nomor HP harus diisi.',
                'no_hp.unique'             => 'Nomor HP sudah digunakan.',
                'alamat.required'          => 'Alamat harus diisi.',
                'tanggal_lahir.required'   => 'Tanggal lahir harus diisi.',
                'tanggal_lahir.date'       => 'Tanggal lahir harus berupa format tanggal yang valid.',
                'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan dan minimal 1 tahun yang lalu (untuk umur minimal 1 tahun).',
                'jenis_pelanggan.required' => 'Jenis pelanggan harus diisi.',
                'jenis_pelanggan.in'       => 'Jenis pelanggan harus Toko Kecil atau Individu.',
                'password.min'             => 'Password minimal 6 karakter.',
                'password.confirmed'       => 'Konfirmasi password tidak cocok.',
                'foto_user.image'          => 'File harus berupa gambar.',
                'foto_user.mimes'          => 'Format gambar harus jpeg, png, atau jpg.',
                'foto_user.max'            => 'Ukuran gambar maksimal 2MB.',
            ]);

            // Gunakan $request->only() untuk mendapatkan data yang relevan
            // Hapus 'umur' dan tambahkan 'tanggal_lahir'
            $data = $request->only(['nama', 'email', 'no_hp', 'alamat', 'tanggal_lahir', 'jenis_pelanggan']);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('foto_user')) {
                // Hapus file lama jika ada
                if ($user->foto_user && Storage::disk('public')->exists($user->foto_user)) {
                    Storage::disk('public')->delete($user->foto_user);
                }

                $data['foto_user'] = $request->file('foto_user')->store('foto_user', 'public');
            }

            $user->update($data);

            return redirect()->route('mobile.profile_pelanggan.index')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi update profil gagal', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui profil pelanggan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui profil.');
        }
    }
}
