<?php

namespace App\Http\Controllers;

use App\Models\HargaProduk;
use App\Models\Produk;
use App\Models\Satuan;
use Illuminate\Http\Request;

class HargaProdukController extends Controller
{
    public function index()
    {
        $hargaProduk = HargaProduk::with(['produk', 'satuan'])->latest()->get();
        return view('harga_produk.index', compact('hargaProduk'));
        
    }

    public function create()
    {
        $produk = Produk::all();
        $satuan = Satuan::all();
        return view('harga_produk.create', compact('produk', 'satuan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'satuan_id' => 'required|exists:satuans,id',
            'jenis_pelanggan' => 'required|in:Toko Kecil,Individu',
            'harga' => 'required|numeric|min:0',
        ]);

        HargaProduk::create($validated);

        return redirect()->route('harga_produk.index')->with('success', 'Harga produk berhasil ditambahkan.');
    }

    public function show(HargaProduk $hargaProduk)
    {
        return view('harga_produk.show', compact('hargaProduk'));
    }

    public function edit(HargaProduk $hargaProduk)
    {
        $produk = Produk::all();
        $satuan = Satuan::all();
        return view('harga_produk.edit', compact('hargaProduk', 'produk', 'satuan'));
    }

    public function update(Request $request, HargaProduk $hargaProduk)
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'satuan_id' => 'required|exists:satuans,id',
            'jenis_pelanggan' => 'required|in:Toko Kecil,Individu',
            'harga' => 'required|numeric|min:0',
        ]);

        $hargaProduk->update($validated);

        return redirect()->route('harga_produk.index')->with('success', 'Harga produk berhasil diperbarui.');
    }

    public function destroy(HargaProduk $hargaProduk)
    {
        $hargaProduk->delete();
        return redirect()->route('harga_produk.index')->with('success', 'Harga produk berhasil dihapus.');
    }
}
