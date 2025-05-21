<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use App\Models\Produk;
use Illuminate\Http\Request;

class SatuanController extends Controller
{
    public function index()
    {
        // Tampilkan semua satuan dengan data produk terkait
        $satuans = Satuan::with('produk')->get();

        return view('satuan.index', compact('satuans'));
    }

    public function create()
    {
        // Ambil semua produk agar bisa pilih produk saat buat satuan
        $produks = Produk::all();

        return view('satuan.create', compact('produks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'nama_satuan' => 'required|string|max:100',
            'konversi_ke_satuan_utama' => 'required|numeric|min:0.0001',
            'level' => 'required|integer|between:1,5',  // validasi level
        ]);

        Satuan::create([
            'produk_id' => $request->produk_id,
            'nama_satuan' => $request->nama_satuan,
            'konversi_ke_satuan_utama' => $request->konversi_ke_satuan_utama,
            'level' => $request->level,
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil ditambahkan.');
    }



    public function edit($id)
    {
        $satuan = Satuan::findOrFail($id);
        $produks = Produk::all();

        return view('satuan.edit', compact('satuan', 'produks'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'nama_satuan' => 'required|string|max:100',
            'konversi_ke_satuan_utama' => 'required|numeric|min:0.0001',
            'level' => 'required|integer|between:1,5',  // batasan level 1 sampai 5
        ]);

        $satuan = Satuan::findOrFail($id);

        $satuan->update([
            'produk_id' => $request->produk_id,
            'nama_satuan' => $request->nama_satuan,
            'konversi_ke_satuan_utama' => $request->konversi_ke_satuan_utama,
            'level' => $request->level, // ambil nilai level dari input
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diperbarui.');
    }



    public function destroy($id)
    {
        $satuan = Satuan::findOrFail($id);
        $satuan->delete();

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil dihapus.');
    }

    public function getSatuanByProduk($id)
    {
        try {
            $satuans = \App\Models\Satuan::where('produk_id', $id)->get();

            return response()->json([
                'success' => true,
                'data' => $satuans
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal ambil satuan untuk produk ID ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data satuan.'
            ], 500);
        }
    }
}
