<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class KeranjangController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        Log::info('=== MASUK KE FUNCTION index KERANJANG ===');


        if ($user->role === 'admin') {
            // Admin lihat semua keranjang pelanggan
            $keranjangs = Keranjang::with(['user', 'produk', 'satuan'])->get();
            return view('keranjang.keranjang_admin.index', compact('keranjangs'));
        } else {
            // Pelanggan lihat keranjang sendiri
            $keranjangs = Keranjang::with(['produk', 'satuan'])
                ->where('user_id', $user->id)
                ->get();
            return view('keranjang.keranjang_pelanggan.index', compact('keranjangs'));
        }
    }

    // di controller KeranjangController@create
    public function create()
    {
        $user = Auth::user();
        $jenis = $user->jenis_pelanggan ?? 'Individu';
        $produks = \App\Models\Produk::with(['satuans', 'hargaProduks' => function ($q) use ($jenis) {
            $q->where('jenis_pelanggan', $jenis);
        }])->get();
        return view('keranjang.keranjang_pelanggan.create', compact('produks', 'jenis'));
    }



    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Admin tidak dapat menambah keranjang.');
        }

        $produk_ids = $request->input('produk_id', []);
        $satuan_ids = $request->input('satuan_id', []);
        $jumlahs = $request->input('jumlah', []);

        DB::beginTransaction();
        try {
            foreach ($produk_ids as $i => $produk_id) {
                $satuan_id = $satuan_ids[$i] ?? null;
                $jumlah = floatval($jumlahs[$i] ?? 0);
                if (!$produk_id || !$satuan_id || $jumlah <= 0) continue;

                // Cek jika sudah ada di keranjang, tambahkan, bukan replace!
                $keranjang = \App\Models\Keranjang::where('user_id', $user->id)
                    ->where('produk_id', $produk_id)
                    ->where('satuan_id', $satuan_id)
                    ->first();
                if ($keranjang) {
                    $keranjang->jumlah += $jumlah;
                    $keranjang->save();
                } else {
                    \App\Models\Keranjang::create([
                        'user_id' => $user->id,
                        'produk_id' => $produk_id,
                        'satuan_id' => $satuan_id,
                        'jumlah' => $jumlah,
                    ]);
                }
            }
            DB::commit();

            return redirect()->route('keranjang.index')->with('success', 'Item berhasil ditambahkan ke keranjang.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan item ke keranjang: ' . $e->getMessage());
        }
    }



    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Admin tidak dapat mengubah keranjang.');
        }

        $keranjang = Keranjang::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'jumlah' => 'required|numeric|min:0.01',
        ]);

        $keranjang->jumlah = $request->jumlah;
        $keranjang->save();

        return redirect()->route('keranjang.index')->with('success', 'Jumlah item di keranjang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Admin tidak dapat menghapus keranjang.');
        }

        $item = Keranjang::where('user_id', $user->id)->findOrFail($id);
        $item->delete();

        return redirect()->route('keranjang.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }
}
