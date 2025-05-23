<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
            'umur'            => 'required|integer|min:1',
            'jenis_pelanggan' => 'required|in:Toko Kecil,Individu',
            'password'        => 'required|string|min:6|confirmed',
        ], [
            'nama.required'            => 'Nama harus diisi.',
            'email.required'           => 'Email harus diisi.',
            'email.email'              => 'Format email tidak valid.',
            'email.unique'             => 'Email sudah digunakan.',
            'no_hp.required'           => 'Nomor HP harus diisi.',
            'no_hp.unique'             => 'Nomor HP sudah digunakan.',
            'alamat.required'          => 'Alamat harus diisi.',
            'umur.required'            => 'Umur harus diisi.',
            'umur.integer'             => 'Umur harus berupa angka.',
            'umur.min'                 => 'Umur minimal 1.',
            'jenis_pelanggan.required' => 'Jenis pelanggan harus diisi.',
            'jenis_pelanggan.in'       => 'Jenis pelanggan harus Toko Kecil atau Individu.',
            'password.required'        => 'Password harus diisi.',
            'password.min'             => 'Password minimal 6 karakter.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
        ]);

        try {
            User::create([
                'nama'            => $request->nama,
                'email'           => $request->email,
                'no_hp'           => $request->no_hp,
                'alamat'          => $request->alamat,
                'umur'            => $request->umur,
                'jenis_pelanggan' => $request->jenis_pelanggan,
                'role'            => 'pelanggan',
                'password'        => Hash::make($request->password),
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
                'umur'            => 'required|integer|min:1',
                'jenis_pelanggan' => 'required|in:Toko Kecil,Individu',
                'password'        => 'nullable|string|min:6|confirmed',
            ], [
                'nama.required'            => 'Nama harus diisi.',
                'email.required'           => 'Email harus diisi.',
                'email.email'              => 'Format email tidak valid.',
                'email.unique'             => 'Email sudah digunakan.',
                'no_hp.required'           => 'Nomor HP harus diisi.',
                'no_hp.unique'             => 'Nomor HP sudah digunakan.',
                'alamat.required'          => 'Alamat harus diisi.',
                'umur.required'            => 'Umur harus diisi.',
                'umur.integer'             => 'Umur harus berupa angka.',
                'umur.min'                 => 'Umur minimal 1.',
                'jenis_pelanggan.required' => 'Jenis pelanggan harus diisi.',
                'jenis_pelanggan.in'       => 'Jenis pelanggan harus Toko Kecil atau Individu.',
                'password.min'             => 'Password minimal 6 karakter.',
                'password.confirmed'       => 'Konfirmasi password tidak cocok.',
            ]);

            $data = $request->only(['nama', 'email', 'no_hp', 'alamat', 'umur', 'jenis_pelanggan']);
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
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
            $pelanggan->delete();

            return redirect()->route('pelanggan.index')->with('success', 'Pelanggan berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pelanggan: ' . $e->getMessage());
            return redirect()->route('pelanggan.index')->with('error', 'Gagal menghapus pelanggan.');
        }
    }
}
