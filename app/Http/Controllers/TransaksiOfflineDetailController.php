<?php

namespace App\Http\Controllers;

use App\Models\TransaksiOfflineDetail;
use App\Models\Produk;
use App\Models\TransaksiOffline;
use Illuminate\Http\Request;

class TransaksiOfflineDetailController extends Controller
{

    public function index()
    {
        $transaksi_detail = TransaksiOfflineDetail::all();
        return view('transaksi_offline_detail.index', compact('transaksi_detail'));
    }



    // public function create($transaksiId)
    // {
    //     $produk = Produk::all();

    //     return view('transaksi_offline_detail.create', compact('produk', 'transaksiId'));
    // }

    // public function show($id)
    // {
    //     // Ambil data detail transaksi berdasarkan ID transaksi
    //     $detail = TransaksiOfflineDetail::with('produk')->where('transaksi_id', $id)->get();

    //     // Kirim ke view
    //     return view('transaksi_offline.detail', compact('detail'));
    // }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'transaksi_id' => 'required|exists:transaksi_offline,id',
    //         'produk_id'    => 'required|exists:produks,id',
    //         'jumlah'       => 'required|integer|min:1',
    //         'harga'        => 'required|numeric|min:0',
    //     ]);

    //     $subtotal = $request->jumlah * $request->harga;

    //     TransaksiOfflineDetail::create([
    //         'transaksi_id' => $request->transaksi_id,
    //         'produk_id'    => $request->produk_id,
    //         'jumlah'       => $request->jumlah,
    //         'harga'        => $request->harga,
    //         'subtotal'     => $subtotal,
    //     ]);

    //     return redirect()->route('transaksi-offline.show', $request->transaksi_id)
    //         ->with('success', 'Produk berhasil ditambahkan ke transaksi.');
    // }

    // public function destroy(TransaksiOfflineDetail $detail)
    // {
    //     $transaksiId = $detail->transaksi_id;
    //     $detail->delete();

    //     return redirect()->route('transaksi-offline.show', $transaksiId)
    //         ->with('success', 'Item berhasil dihapus dari transaksi.');
    // }
}
