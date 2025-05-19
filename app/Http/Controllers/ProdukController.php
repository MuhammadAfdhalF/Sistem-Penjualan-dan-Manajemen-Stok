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
        $produk = Produk::all();
        return view('produk.index', compact('produk'));
    }

    public function create()
    {
        return view('produk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk'    => 'required|string|max:255',
            'deskripsi'      => 'required|string|max:500',
            'gambar'         => 'required|image|mimes:jpeg,png,jpg',
            'harga_normal'   => 'required|numeric|min:0',
            'harga_grosir'   => 'required|numeric|min:0',
            'stok'           => 'required|numeric|min:0',
            //'rop'          => 'required|numeric|min:0',  // Hapus ini
            'kategori'       => 'required|string|max:255',
            'satuan'         => 'required|string|in:bks,pcs,kg,liter,box',
            'lead_time'      => 'required|integer|min:0',
            'daily_usage'    => 'required|integer|min:0',
            'safety_stock'   => 'required|integer|min:0',
        ]);

        // Hitung ROP otomatis
        $rop = ($request->lead_time * $request->daily_usage) + $request->safety_stock;

        $gambar = $request->file('gambar');
        $fileName = 'produk_' . Str::slug($request->nama_produk) . '_' . $gambar->getClientOriginalName();

        try {
            $gambar->storeAs('gambar_produk', $fileName, 'public');

            Produk::create([
                'nama_produk'   => $request->nama_produk,
                'deskripsi'     => $request->deskripsi,
                'harga_normal'  => $request->harga_normal,
                'harga_grosir'  => $request->harga_grosir,
                'stok'          => $request->stok,
                'rop'           => $rop,  // pakai hasil hitung
                'kategori'      => $request->kategori,
                'gambar'        => $fileName,
                'satuan'        => $request->satuan,
                'lead_time'     => $request->lead_time,
                'daily_usage'   => $request->daily_usage,
                'safety_stock'  => $request->safety_stock,
            ]);

            DB::commit();

            // Panggil command update daily_usage dan rop setelah transaksi berhasil
            Artisan::call('produk:update-dailyusage-rop');
            return redirect()->route('produk.index')->with('success', 'Data produk berhasil disimpan.');
        } catch (\Exception $e) {
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
        $request->validate([
            'nama_produk'    => 'required|string|max:255',
            'deskripsi'      => 'required|string|max:500',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg',
            'harga_normal'   => 'required|numeric|min:0',
            'harga_grosir'   => 'required|numeric|min:0',
            'stok'           => 'required|numeric|min:0',
            //'rop'          => 'required|numeric|min:0', // hapus validasi rop
            'kategori'       => 'required|string|max:255',
            'satuan'         => 'required|string|in:bks,pcs,kg,liter,box',
            'lead_time'      => 'required|integer|min:0',
            'daily_usage'    => 'required|integer|min:0',
            'safety_stock'   => 'required|integer|min:0',
        ]);

        $produk = Produk::findOrFail($id);

        // Hitung ulang ROP
        $rop = ($request->lead_time * $request->daily_usage) + $request->safety_stock;

        $fileName = $produk->gambar; // default pakai gambar lama

        if ($request->hasFile('gambar')) {
            $gambar = $request->file('gambar');
            $fileName = 'produk_' . Str::slug($request->nama_produk) . '_' . $gambar->getClientOriginalName();
            $gambar->storeAs('gambar_produk', $fileName, 'public');
            // Jika mau hapus gambar lama bisa ditambah kode di sini
        }

        try {
            $produk->update([
                'nama_produk'   => $request->nama_produk,
                'deskripsi'     => $request->deskripsi,
                'harga_normal'  => $request->harga_normal,
                'harga_grosir'  => $request->harga_grosir,
                'stok'          => $request->stok,
                'rop'           => $rop,  // simpan hasil hitung
                'kategori'      => $request->kategori,
                'gambar'        => $fileName,
                'satuan'        => $request->satuan,
                'lead_time'     => $request->lead_time,
                'daily_usage'   => $request->daily_usage,
                'safety_stock'  => $request->safety_stock,
            ]);

            DB::commit();

            // Panggil command update daily_usage dan rop setelah transaksi berhasil
            Artisan::call('produk:update-dailyusage-rop');
            return redirect()->route('produk.index')->with('success', 'Data produk berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data. ' . $e->getMessage());
        }
    }



    public function destroy(Produk $produk)
    {
        try {
            if ($produk->gambar && Storage::disk('public')->exists('gambar_produk/' . $produk->gambar)) {
                Storage::disk('public')->delete('gambar_produk/' . $produk->gambar);
            }

            $produk->delete();
            DB::commit();

            // Panggil command update daily_usage dan rop setelah transaksi berhasil
            Artisan::call('produk:update-dailyusage-rop');
            return redirect()->route('produk.index')->with('success', 'Data produk berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('produk.index')->with('error', 'Gagal menghapus data produk. Silakan coba lagi.');
        }
    }
}
