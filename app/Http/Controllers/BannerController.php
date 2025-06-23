<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $banners = Banner::orderBy('urutan')->orderBy('id')->get();
            return view('banner.index', compact('banners'));
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data banner: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menampilkan data banner.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('banner.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_banner' => 'required|string|max:255',
            'gambar_url'  => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'urutan'      => 'nullable|integer|min:0',
            'is_aktif'    => 'boolean',
        ], [
            'nama_banner.required' => 'Nama banner harus diisi.',
            'nama_banner.string'   => 'Nama banner harus berupa teks.',
            'nama_banner.max'      => 'Nama banner maksimal 255 karakter.',
            'gambar_url.required'  => 'Gambar banner harus diunggah.',
            'gambar_url.image'     => 'File harus berupa gambar.',
            'gambar_url.mimes'     => 'Format gambar harus jpeg, png, jpg, gif, atau svg.',
            'gambar_url.max'       => 'Ukuran gambar maksimal 2MB.',
            'urutan.integer'       => 'Urutan harus berupa angka.',
            'urutan.min'           => 'Urutan minimal 0.',
            'is_aktif.boolean'     => 'Status aktif tidak valid.',
        ]);

        try {
            $gambarPath = null;
            if ($request->hasFile('gambar_url')) {
                $gambarPath = $request->file('gambar_url')->store('banner', 'public');
            }

            Banner::create([
                'nama_banner' => $request->nama_banner,
                'gambar_url'  => $gambarPath,
                'urutan'      => $request->urutan ?? 0,
                'is_aktif'    => $request->has('is_aktif'),
            ]);

            return redirect()->route('banner.index')->with('success', 'Banner berhasil ditambahkan!');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan banner: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan banner.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            return view('banner.show', compact('banner'));
        } catch (\Exception $e) {
            Log::error('Gagal menampilkan banner: ' . $e->getMessage());
            return redirect()->route('banner.index')->with('error', 'Data banner tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            return view('banner.edit', compact('banner'));
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data banner untuk edit: ' . $e->getMessage());
            return redirect()->route('banner.index')->with('error', 'Data banner tidak ditemukan.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $banner = Banner::findOrFail($id);

            $request->validate([
                'nama_banner' => 'required|string|max:255',
                'gambar_url'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'urutan'      => 'nullable|integer|min:0',
                'is_aktif'    => 'boolean',
            ], [
                'nama_banner.required' => 'Nama banner harus diisi.',
                'nama_banner.string'   => 'Nama banner harus berupa teks.',
                'nama_banner.max'      => 'Nama banner maksimal 255 karakter.',
                'gambar_url.image'     => 'File harus berupa gambar.',
                'gambar_url.mimes'     => 'Format gambar harus jpeg, png, jpg, gif, atau svg.',
                'gambar_url.max'       => 'Ukuran gambar maksimal 2MB.',
                'urutan.integer'       => 'Urutan harus berupa angka.',
                'urutan.min'           => 'Urutan minimal 0.',
                'is_aktif.boolean'     => 'Status aktif tidak valid.',
            ]);

            $data = $request->only(['nama_banner', 'urutan']);
            $data['is_aktif'] = $request->has('is_aktif');

            if ($request->hasFile('gambar_url')) {
                if ($banner->gambar_url && Storage::disk('public')->exists($banner->gambar_url)) {
                    Storage::disk('public')->delete($banner->gambar_url);
                }
                $data['gambar_url'] = $request->file('gambar_url')->store('banner', 'public');
            }

            $banner->update($data);

            return redirect()->route('banner.index')->with('success', 'Banner berhasil diperbarui!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi update banner gagal', [
                'errors' => $e->errors(),
                'input' => $request->all(),
                'id' => $id,
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui banner: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data banner.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $banner = Banner::findOrFail($id);

            if ($banner->gambar_url && Storage::disk('public')->exists($banner->gambar_url)) {
                Storage::disk('public')->delete($banner->gambar_url);
            }

            $banner->delete();

            return redirect()->route('banner.index')->with('success', 'Banner berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus banner: ' . $e->getMessage());
            return redirect()->route('banner.index')->with('error', 'Gagal menghapus banner.');
        }
    }
}
