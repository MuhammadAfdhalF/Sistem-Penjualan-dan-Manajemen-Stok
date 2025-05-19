<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use App\Models\Produk;  // Pastikan model Produk di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class StokController extends Controller
{
    public function index()
    {
        // Mengambil semua data stok dari database
        $stok = Stok::all();

        // Mengirim data stok ke view 'stok.index'
        return view('stok.index', compact('stok'));
    }

    public function create()
    {
        // Ambil semua produk yang tersedia
        $produk = Produk::all();

        // Kirim data produk ke view
        return view('stok.create', compact('produk'));
    }


    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'produk_id'  => 'required|exists:produks,id',
            'jenis'      => 'required|in:masuk,keluar',
            'jumlah'     => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ], [
            'produk_id.required' => 'Produk harus dipilih.',
            'produk_id.exists'   => 'Produk tidak ditemukan.',
            'jenis.required'     => 'Jenis stok harus diisi.',
            'jenis.in'           => 'Jenis stok harus "masuk" atau "keluar".',
            'jumlah.required'    => 'Jumlah harus diisi.',
            'jumlah.integer'     => 'Jumlah harus berupa angka.',
            'jumlah.min'         => 'Jumlah minimal 1.',
        ]);

        // Simpan data ke tabel stok
        $stok = Stok::create([
            'produk_id'  => $request->produk_id,
            'jenis'      => $request->jenis,
            'jumlah'     => $request->jumlah,
            'keterangan' => $request->keterangan,
        ]);

        // Update stok produk
        $produk = Produk::find($request->produk_id);
        if ($request->jenis == 'masuk') {
            $produk->stok += $request->jumlah;  // Menambah stok produk
        } elseif ($request->jenis == 'keluar') {
            $produk->stok -= $request->jumlah;  // Mengurangi stok produk
        }
        $produk->save();
        DB::commit();

        // Panggil command update daily_usage dan rop setelah transaksi berhasil
        Artisan::call('produk:update-dailyusage-rop');
        return redirect()->route('stok.index')->with('success', 'Data stok berhasil disimpan.');
    }




    public function show(Stok $stok)
    {
        //
    }


    public function edit($id)
    {
        // Mencari data stok berdasarkan ID
        $stok = Stok::find($id);

        // Ambil semua produk yang tersedia
        $produk = Produk::all();

        // Mengembalikan tampilan untuk mengedit stok dengan membawa data stok dan produk
        return view('stok.edit', compact('stok', 'produk'));
    }


    // Fungsi update untuk memperbarui data stok
    public function update(Request $request, Stok $stok)
    {
        $request->validate([
            'produk_id'  => 'required|exists:produks,id',
            'jenis'      => 'required|in:masuk,keluar',
            'jumlah'     => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $produk = Produk::find($request->produk_id);

            // Hitung stok lama dan kembalikan ke kondisi awal
            if ($stok->jenis === 'masuk') {
                $produk->stok -= $stok->jumlah; // Batalkan penambahan sebelumnya
            } elseif ($stok->jenis === 'keluar') {
                $produk->stok += $stok->jumlah; // Batalkan pengurangan sebelumnya
            }

            // Update data stok
            $stok->update([
                'produk_id'  => $request->produk_id,
                'jenis'      => $request->jenis,
                'jumlah'     => $request->jumlah,
                'keterangan' => $request->keterangan,
            ]);

            // Terapkan perubahan baru
            if ($request->jenis === 'masuk') {
                $produk->stok += $request->jumlah;
            } elseif ($request->jenis === 'keluar') {
                $produk->stok -= $request->jumlah;
            }

            $produk->save();
            DB::commit();

            // Panggil command update daily_usage dan rop setelah transaksi berhasil
            Artisan::call('produk:update-dailyusage-rop');
            return redirect()->route('stok.index')->with('success', 'Data stok berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data stok. ' . $e->getMessage());
        }
    }




    public function destroy(Stok $stok)
    {
        try {
            // Ambil produk terkait
            $produk = $stok->produk;

            // Kembalikan stok produk ke kondisi sebelum entri stok ini dibuat
            if ($stok->jenis == 'masuk') {
                $produk->stok -= $stok->jumlah;
            } elseif ($stok->jenis == 'keluar') {
                $produk->stok += $stok->jumlah;
            }

            // Simpan perubahan stok produk
            $produk->save();

            // Hapus data stok
            $stok->delete();

            DB::commit();

            // Panggil command update daily_usage dan rop setelah transaksi berhasil
            Artisan::call('produk:update-dailyusage-rop');
            return redirect()->route('stok.index')->with('success', 'Data stok berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('stok.index')->with('error', 'Gagal menghapus data stok. Silakan coba lagi.');
        }
    }
}
