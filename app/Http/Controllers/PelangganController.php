<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Pastikan Carbon diimport untuk validasi tanggal

class PelangganController extends Controller
{
    public function index()
    {
        try {
            $pelanggan = User::where('role', 'pelanggan')->get();
            return view('pelanggan.index', compact('pelanggan'));
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data pelanggan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menampilkan data pelanggan.');
        }
    }

    public function create()
    {
        return view('pelanggan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users',
            'no_hp'           => 'required|unique:users',
            'alamat'          => 'required|string',
            'tanggal_lahir'   => 'required|date|before_or_equal:' . Carbon::now()->subYears(1)->format('Y-m-d'), // Validasi tanggal lahir
            'jenis_pelanggan' => 'required|in:Toko Kecil,Individu',
            'password'        => 'required|string|min:6|confirmed',
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
            'password.required'        => 'Password harus diisi.',
            'password.min'             => 'Password minimal 6 karakter.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
            'foto_user.image'          => 'File harus berupa gambar.',
            'foto_user.mimes'          => 'Format gambar harus jpeg, png, atau jpg.',
            'foto_user.max'            => 'Ukuran gambar maksimal 2MB.',
        ]);

        try {
            $foto = null;
            if ($request->hasFile('foto_user')) {
                $foto = $request->file('foto_user')->store('foto_user', 'public');
            }

            User::create([
                'nama'            => $request->nama,
                'email'           => $request->email,
                'no_hp'           => $request->no_hp,
                'alamat'          => $request->alamat,
                'tanggal_lahir'   => $request->tanggal_lahir, // Menggunakan tanggal_lahir
                'jenis_pelanggan' => $request->jenis_pelanggan,
                'role'            => 'pelanggan',
                'password'        => Hash::make($request->password),
                'foto_user'       => $foto,
            ]);

            return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan pelanggan: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan pelanggan.');
        }
    }


    public function show(string $id)
    {
        try {
            $pelanggan = User::where('role', 'pelanggan')->findOrFail($id);
            return view('pelanggan.show', compact('pelanggan'));
        } catch (\Exception $e) {
            Log::error('Gagal menampilkan pelanggan: ' . $e->getMessage());
            return redirect()->route('pelanggan.index')->with('error', 'Data pelanggan tidak ditemukan.');
        }
    }

    public function edit(string $id)
    {
        try {
            $pelanggan = User::where('role', 'pelanggan')->findOrFail($id);
            return view('pelanggan.edit', compact('pelanggan'));
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data pelanggan untuk edit: ' . $e->getMessage());
            return redirect()->route('pelanggan.index')->with('error', 'Data pelanggan tidak ditemukan.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $pelanggan = User::where('role', 'pelanggan')->findOrFail($id);

            $request->validate([
                'nama'            => 'required|string|max:255',
                'email'           => 'required|email|unique:users,email,' . $pelanggan->id,
                'no_hp'           => 'required|unique:users,no_hp,' . $pelanggan->id,
                'alamat'          => 'required|string',
                'tanggal_lahir'   => 'required|date|before_or_equal:' . Carbon::now()->subYears(1)->format('Y-m-d'), // Validasi tanggal lahir
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
            $data = $request->only(['nama', 'email', 'no_hp', 'alamat', 'tanggal_lahir', 'jenis_pelanggan']);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('foto_user')) {
                // Hapus foto lama jika ada
                if ($pelanggan->foto_user && \Storage::disk('public')->exists($pelanggan->foto_user)) {
                    \Storage::disk('public')->delete($pelanggan->foto_user);
                }
                $data['foto_user'] = $request->file('foto_user')->store('foto_user', 'public');
            }

            $pelanggan->update($data);

            return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi update pelanggan gagal', [
                'errors' => $e->errors(),
                'input' => $request->all(),
                'id' => $id,
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui pelanggan: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data pelanggan.');
        }
    }

    public function destroy(string $id)
    {
        try {
            $pelanggan = User::where('role', 'pelanggan')->findOrFail($id);

            // Hapus file foto jika ada
            if ($pelanggan->foto_user && \Storage::disk('public')->exists($pelanggan->foto_user)) {
                \Storage::disk('public')->delete($pelanggan->foto_user);
            }

            $pelanggan->delete();

            return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pelanggan: ' . $e->getMessage());
            return redirect()->route('pelanggan.index')->with('error', 'Gagal menghapus pelanggan.');
        }
    }
}
