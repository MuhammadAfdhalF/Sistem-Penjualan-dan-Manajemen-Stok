<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ProdukController extends Controller
{
    public function index()
    {
        // Eager load relasi satuans agar data satuan tersedia di view
        $produk = Produk::with('satuans')->get();

        // Filter produk dengan stok <= rop (menggunakan method di model)
        $produkMenipis = $produk->filter(fn($item) => $item->isStokDiBawahROP());

        return view('produk.index', compact('produk', 'produkMenipis'));
    }

    public function create()
    {
        return view('produk.create');
    }

    public function store(Request $request)
    {
        // Log semua request masuk untuk debug
        Log::info('Request Produk Store:', $request->all());

        // Debug cepat: lihat semua inputan di browser (hapus saat production)
        // dd($request->all());

        $request->validate([
            'nama_produk'    => 'required|string|max:255',
            'deskripsi'      => 'required|string|max:500',
            'gambar'         => 'required|image|mimes:jpeg,png,jpg',
            'harga_normal'   => 'required|numeric|min:0',
            'harga_grosir'   => 'nullable|numeric|min:0',
            'stok'           => 'required|numeric|min:0',
            'kategori'       => 'required|string|max:255',
            'satuan_utama'   => 'required|string|max:50',
            'lead_time'      => 'required|integer|min:0',
            'safety_stock'   => 'required|numeric|min:0',
        ]);

        if (!$request->hasFile('gambar')) {
            return back()->with('error', 'Gambar tidak ditemukan dalam permintaan.');
        }

        $gambar = $request->file('gambar');

        if (!$gambar->isValid()) {
            return back()->with('error', 'File gambar tidak valid.');
        }

        $slugNama = Str::slug($request->nama_produk) ?: 'produk';
        $fileName = 'produk_' . $slugNama . '_' . time() . '.' . $gambar->getClientOriginalExtension();

        try {
            DB::beginTransaction();

            $gambar->storeAs('gambar_produk', $fileName, 'public');

            // $rop = ($request->lead_time * $request->daily_usage) + $request->safety_stock;

            Produk::create([
                'nama_produk'   => $request->nama_produk,
                'deskripsi'     => $request->deskripsi,
                'harga_normal'  => $request->harga_normal,
                'harga_grosir'  => $request->harga_grosir,
                'stok'          => $request->stok,
                'kategori'      => $request->kategori,
                'gambar'        => $fileName,
                'satuan_utama'  => $request->satuan_utama,
                'lead_time'     => $request->lead_time,
                'daily_usage' =>  0,
                'safety_stock'  => $request->safety_stock,
            ]);

            DB::commit();

            // Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('produk.index')->with('success', 'Data produk berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Catat error lengkap ke log
            Log::error('Produk Store Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $produk = Produk::findOrFail($id);
        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, $id)
    {

        // Log semua request masuk untuk debug
        Log::info('Request Produk Update:', $request->all()); // ganti nama log
        $request->validate([
            'nama_produk'    => 'required|string|max:255',
            'deskripsi'      => 'required|string|max:500',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg',
            'harga_normal'   => 'required|numeric|min:0',
            'harga_grosir'   => 'nullable|numeric|min:0',
            'stok'           => 'required|numeric|min:0',
            // hapus validasi rop
            'kategori'       => 'required|string|max:255',
            'satuan_utama'   => 'required|string|max:50',
            'lead_time'      => 'required|integer|min:0',
            'daily_usage'    => 'required|numeric|min:0',
            'safety_stock'   => 'required|numeric|min:0',
        ]);

        $produk = Produk::findOrFail($id);

        try {
            DB::beginTransaction();

            // $rop = ($request->lead_time * $request->daily_usage) + $request->safety_stock;

            $fileName = $produk->gambar;

            if ($request->hasFile('gambar')) {
                $gambar = $request->file('gambar');
                $fileName = 'produk_' . Str::slug($request->nama_produk) . '_' . time() . '.' . $gambar->getClientOriginalExtension();
                $gambar->storeAs('gambar_produk', $fileName, 'public');
            }

            $produk->update([
                'nama_produk'   => $request->nama_produk,
                'deskripsi'     => $request->deskripsi,
                'harga_normal'  => $request->harga_normal,
                'harga_grosir'  => $request->harga_grosir,
                'stok'          => $request->stok,
                'kategori'      => $request->kategori,
                'gambar'        => $fileName,
                'satuan_utama'  => $request->satuan_utama,
                'lead_time'     => $request->lead_time,
                'daily_usage'   => $request->daily_usage,
                'safety_stock'  => $request->safety_stock,
            ]);

            DB::commit();

            // Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('produk.index')->with('success', 'Data produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data. ' . $e->getMessage());
        }
    }

    public function destroy(Produk $produk)
    {
        DB::beginTransaction();

        try {
            // Hapus gambar jika ada
            if ($produk->gambar && Storage::disk('public')->exists('gambar_produk/' . $produk->gambar)) {
                Storage::disk('public')->delete('gambar_produk/' . $produk->gambar);
            }

            $produk->delete();

            DB::commit();

            Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('produk.index')->with('success', 'Data produk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('produk.index')->with('error', 'Gagal menghapus data produk. ' . $e->getMessage());
        }
    }
}
