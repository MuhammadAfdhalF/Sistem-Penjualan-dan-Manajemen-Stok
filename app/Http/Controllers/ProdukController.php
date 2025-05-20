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
        // Contoh ambil semua satuan bertingkat yang bisa dipilih
        // Misal kamu buat relasi ke model Satuan, dan kamu ingin ambil semua satuan terkait, atau bisa ambil semua dari tabel satuan

        $satuanBertingkat = \App\Models\Satuan::all();

        return view('produk.create', compact('satuanBertingkat'));
    }

    public function store(Request $request)
    {
        Log::info('Request Produk Store:', $request->all());

        // Atur rules dasar
        $rules = [
            'nama_produk'    => 'required|string|max:255',
            'deskripsi'      => 'required|string|max:500',
            'gambar'         => 'required|image|mimes:jpeg,png,jpg',
            'harga_normal'   => 'required|numeric|min:0',
            'harga_grosir'   => 'nullable|numeric|min:0',
            'kategori'       => 'required|string|max:255',
            'satuan_utama'   => 'required|string|max:50',
            'lead_time'      => 'required|integer|min:0',
            'safety_stock'   => 'required|numeric|min:0',
            'mode_stok'      => 'required|in:utama,bertahap',
        ];

        // Validasi stok sesuai mode stok
        if ($request->mode_stok === 'utama') {
            $rules['stok'] = 'required|numeric|min:0';
            $rules['stok_bertahap'] = 'nullable';
        } else {
            $rules['stok'] = 'nullable';
            $rules['stok_bertahap'] = 'required|array';
            $rules['stok_bertahap.*'] = 'numeric|min:0';
        }

        $request->validate($rules);

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

            // Hitung stok akhir jika mode bertahap
            $stokFinal = $request->stok;
            if ($request->mode_stok === 'bertahap') {
                $stokBertahap = $request->stok_bertahap;
                $stokFinal = 0;
                foreach ($stokBertahap as $key => $qty) {
                    if ($key === 'utama') {
                        $konversi = 1;
                    } else {
                        // Jika perlu ambil konversi dari database satuan, sesuaikan di sini.
                        // Sebagai contoh, set default 1 jika tidak ada data.
                        $konversi = 1;
                    }
                    $stokFinal += ((float)$qty) * $konversi;
                }
            }

            Produk::create([
                'nama_produk'   => $request->nama_produk,
                'deskripsi'     => $request->deskripsi,
                'harga_normal'  => $request->harga_normal,
                'harga_grosir'  => $request->harga_grosir,
                'stok'          => $stokFinal,
                'kategori'      => $request->kategori,
                'gambar'        => $fileName,
                'satuan_utama'  => $request->satuan_utama,
                'lead_time'     => $request->lead_time,
                'daily_usage'   => 0,
                'safety_stock'  => $request->safety_stock,
            ]);

            DB::commit();

            return redirect()->route('produk.index')->with('success', 'Data produk berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Produk Store Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data. ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $produk = Produk::with('satuans')->findOrFail($id);

        $satuanBertingkat = $produk->satuans()
            ->orderByDesc('konversi_ke_satuan_utama')
            ->get();

        // Konversi stok total menjadi pecahan satuan bertingkat
        $stokSisa = (int) $produk->stok;
        $stokBertingkatDefault = [];

        foreach ($satuanBertingkat as $satuan) {
            if ($satuan->konversi_ke_satuan_utama <= 0) continue;

            $jumlah = intdiv($stokSisa, $satuan->konversi_ke_satuan_utama);
            $stokSisa = $stokSisa % $satuan->konversi_ke_satuan_utama;

            $stokBertingkatDefault[$satuan->id] = $jumlah;
        }

        // Tambah sisa stok sebagai satuan utama
        $stokBertingkatDefault['utama'] = $stokSisa;

        return view('produk.edit', compact('produk', 'satuanBertingkat', 'stokBertingkatDefault'));
    }



    public function update(Request $request, $id)
    {
        Log::info('Request Produk Update:', $request->all());

        $rules = [
            'nama_produk'    => 'required|string|max:255',
            'deskripsi'      => 'required|string|max:500',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg',
            'harga_normal'   => 'required|numeric|min:0',
            'harga_grosir'   => 'nullable|numeric|min:0',
            'kategori'       => 'required|string|max:255',
            'satuan_utama'   => 'required|string|max:50',
            'lead_time'      => 'required|integer|min:0',
            'daily_usage'    => 'required|numeric|min:0',
            'safety_stock'   => 'required|numeric|min:0',
            'mode_stok'      => 'required|in:utama,bertahap',
        ];

        // Validasi stok dan stok_bertahap sesuai mode_stok
        if ($request->mode_stok === 'utama') {
            $rules['stok'] = 'required|numeric|min:0';
            $rules['stok_bertahap'] = 'nullable';
        } else {
            $rules['stok'] = 'nullable';
            $rules['stok_bertahap'] = 'required|array';
            // opsional: bisa tambahkan validasi tiap elemen stok_bertahap angka min 0
            // contoh:
            $rules['stok_bertahap.*'] = 'numeric|min:0';
        }

        $request->validate($rules);

        $produk = Produk::findOrFail($id);

        try {
            DB::beginTransaction();

            $fileName = $produk->gambar;

            if ($request->hasFile('gambar')) {
                $gambar = $request->file('gambar');
                $fileName = 'produk_' . Str::slug($request->nama_produk) . '_' . time() . '.' . $gambar->getClientOriginalExtension();
                $gambar->storeAs('gambar_produk', $fileName, 'public');
            }

            // Hitung stok jika mode bertingkat
            $stokUpdate = $request->stok;
            if ($request->mode_stok === 'bertahap') {
                $stokBertahap = $request->stok_bertahap;
                $stokUpdate = 0;
                foreach ($stokBertahap as $key => $qty) {
                    if ($key === 'utama') {
                        $konversi = 1;
                    } else {
                        // ambil konversi dari database atau input lain
                        // misal: ambil satuan dari produk dan dapatkan konversi berdasarkan id satuan
                        $satuan = $produk->satuans()->where('id', $key)->first();
                        $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    }
                    $stokUpdate += ((float)$qty) * $konversi;
                }
            }

            $produk->update([
                'nama_produk'   => $request->nama_produk,
                'deskripsi'     => $request->deskripsi,
                'harga_normal'  => $request->harga_normal,
                'harga_grosir'  => $request->harga_grosir,
                'stok'          => $stokUpdate,
                'kategori'      => $request->kategori,
                'gambar'        => $fileName,
                'satuan_utama'  => $request->satuan_utama,
                'lead_time'     => $request->lead_time,
                'daily_usage'   => $request->daily_usage,
                'safety_stock'  => $request->safety_stock,
            ]);

            DB::commit();

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
