<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class PegawaiController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::all();
        return view('pegawai.index', compact('pegawai'));
    }

    public function create()
    {
        return view('pegawai.create');
    }


    public function store(Request $request)
    {
        // Validasi inputan
        $request->validate([
            'nama_pegawai'     => 'required|string|max:255',
            'alamat'           => 'required|string|max:500',
            'foto'             => 'required|image|mimes:jpeg,png,jpg',
            'umur'             => 'required|numeric|between:18,100',
            'tanggal_lahir'    => 'required|date',
            'tempat_lahir'     => 'required|string|max:255',
            'jenis_kelamin'    => 'required|in:laki-laki,perempuan',
        ], [
            'nama_pegawai.required'    => 'Nama pegawai harus diisi.',
            'alamat.required'          => 'Alamat harus diisi.',
            'foto.required'            => 'Foto harus diisi.',
            'umur.required'            => 'Umur harus diisi.',
            'umur.numeric'             => 'Umur harus berupa angka.',
            'tanggal_lahir.required'   => 'Tanggal lahir harus diisi.',
            'tanggal_lahir.date'       => 'Tanggal lahir harus berupa tanggal.',
            'tempat_lahir.required'    => 'Tempat lahir harus diisi.',
            'jenis_kelamin.required'   => 'Jenis kelamin harus diisi.',
            'jenis_kelamin.in'         => 'Jenis kelamin harus salah satu dari: laki-laki atau perempuan.',
        ]);

        // Mengambil file foto dari input
        $foto = $request->file('foto');

        // Menentukan nama file yang unik dengan UUID dan ekstensi yang benar
        $fileName = 'pegawai_' . Str::slug($request->nama_pegawai) . '_' . $foto->getClientOriginalName();

        // Menyimpan file foto ke disk 'public' di folder 'foto_pegawai'
        try {
            // Simpan foto di folder storage/app/public/foto_pegawai
            $path = $foto->storeAs('foto_pegawai', $fileName, 'public');

            // Menyimpan data pegawai ke database, termasuk nama file foto
            Pegawai::create([
                'nama_pegawai'  => $request->nama_pegawai,
                'alamat'        => $request->alamat,
                'umur'          => $request->umur,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tempat_lahir'  => $request->tempat_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'foto'          => $fileName, // Menyimpan nama file foto di database
            ]);

            // Redirect dengan pesan sukses
            return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil disimpan.');
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika gagal menyimpan data
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // dd($pegawai);
        $pegawai = Pegawai::find($id);
        return view('pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $request->validate([
            'nama_pegawai'     => 'required|string|max:255',
            'alamat'           => 'required|string|max:500',
            'umur'             => 'required|numeric|between:18,100',
            'tanggal_lahir'    => 'required|date',
            'tempat_lahir'     => 'required|string|max:255',
            'jenis_kelamin'    => 'required|in:laki-laki,perempuan',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg',
        ]);

        try {
            $data = [
                'nama_pegawai'   => $request->nama_pegawai,
                'alamat'         => $request->alamat,
                'umur'           => $request->umur,
                'tanggal_lahir'  => $request->tanggal_lahir,
                'tempat_lahir'   => $request->tempat_lahir,
                'jenis_kelamin'  => $request->jenis_kelamin,
            ];

            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($pegawai->foto && Storage::disk('public')->exists('foto_pegawai/' . $pegawai->foto)) {
                    Storage::disk('public')->delete('foto_pegawai/' . $pegawai->foto);
                }

                $foto = $request->file('foto');
                $namaFileBaru = 'pegawai_' . $foto->getClientOriginalName();
                $foto->storeAs('public/foto_pegawai', $namaFileBaru);

                $data['foto'] = $namaFileBaru;
            }

            $pegawai->update($data);

            return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data. ' . $e->getMessage());
        }
    }

    public function destroy(Pegawai $pegawai)
    {
        try {
            $pegawai->delete();
            return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('pegawai.index')->with('error', 'Gagal menghapus data pegawai. Silakan coba lagi.');
        }
    }
}
