<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Stok; // Pastikan model Stok diimpor
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Satuan; // Pastikan ini diimpor jika belum
use App\Models\HargaProduk; // Pastikan ini diimpor jika belum

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua kategori unik
        $listKategori = Produk::select('kategori')
            ->distinct()
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->pluck('kategori');

        // Ambil parameter filter dari request
        $filterKategori = $request->kategori;

        // Query produk (filter jika ada kategori) + urut terbaru
        $query = Produk::with(['satuans', 'hargaProduks.satuan'])
            ->latest(); // <-- simple: urutkan data terbaru di atas

        if ($filterKategori) {
            $query->where('kategori', $filterKategori);
        }

        $produk = $query->get();

        // Filter produk dengan stok <= ROP
        $produkMenipis = $produk->filter(fn($item) => $item->isStokDiBawahROP());

        return view('produk.index', compact('produk', 'produkMenipis', 'listKategori', 'filterKategori'));
    }

    public function create()
    {
        // Mengarahkan ke form terpadu yang baru
        return view('produk.create'); // Mengarahkan ke view form terpadu
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi'   => 'required|string|max:500',
            'gambar'      => 'required|image|mimes:jpeg,png,jpg',
            'kategori'    => 'required|string|max:255',
            'lead_time'   => 'required|integer|min:0',
            'satuans.*.nama_satuan' => 'required|string|max:100',
            'satuans.*.level' => 'required|integer|between:1,5',
            'satuans.*.konversi' => 'required|numeric|min:0.0001',
            'satuans.*.harga_toko_kecil' => 'nullable|numeric|min:0',
            'satuans.*.harga_individu' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Upload gambar
            $gambar = $request->file('gambar');
            $fileName = 'produk_' . Str::slug($request->nama_produk) . '_' . time() . '.' . $gambar->getClientOriginalExtension();
            $gambar->storeAs('gambar_produk', $fileName, 'public');

            // Simpan data produk dengan stok awal 0
            $produk = Produk::create([
                'nama_produk' => $validated['nama_produk'],
                'deskripsi'   => $validated['deskripsi'],
                'kategori'    => $validated['kategori'],
                'lead_time'   => $validated['lead_time'],
                'gambar'      => $fileName,
                'stok'        => 0, // Stok awal default 0, akan diisi di tahap 2
                'daily_usage' => 0,
                'safety_stock' => 0,
            ]);

            // Simpan semua data satuan dan harganya
            if (isset($request->satuans) && is_array($request->satuans)) {
                foreach ($request->satuans as $satuanData) {
                    $satuan = $produk->satuans()->create([
                        'nama_satuan' => $satuanData['nama_satuan'],
                        'level'       => $satuanData['level'],
                        'konversi_ke_satuan_utama' => $satuanData['konversi'],
                    ]);

                    if (isset($satuanData['harga_toko_kecil'])) {
                        $satuan->hargaProduk()->create([
                            'produk_id' => $produk->id,
                            'jenis_pelanggan' => 'Toko Kecil',
                            'harga' => $satuanData['harga_toko_kecil'],
                        ]);
                    }
                    if (isset($satuanData['harga_individu'])) {
                        $satuan->hargaProduk()->create([
                            'produk_id' => $produk->id,
                            'jenis_pelanggan' => 'Individu',
                            'harga' => $satuanData['harga_individu'],
                        ]);
                    }
                }
            }
            DB::commit();

            // Tahap 2/2: Arahkan ke form stok awal yang baru
            return redirect()->route('produk.stok_awal.create', ['produk_id' => $produk->id])
                ->with('success', 'Produk berhasil disimpan. Silakan tambahkan stok awalnya.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan produk terpadu: ' . $e->getMessage());
            if (isset($fileName)) {
                Storage::disk('public')->delete('gambar_produk/' . $fileName);
            }
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data. Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Ambil produk, eager load semua relasi yang dibutuhkan
        $produk = Produk::with('satuans.hargaProduk')->findOrFail($id);

        // Kode lama untuk dekonstruksi stok dihapus karena tidak lagi relevan
        // Stok akan dikelola di StokController

        return view('produk.edit', compact('produk'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Request Produk Update:', $request->all());

        // Aturan validasi yang diperbarui
        $rules = [
            'nama_produk'   => 'required|string|max:255',
            'deskripsi'     => 'required|string|max:500',
            'gambar'        => 'nullable|image|mimes:jpeg,png,jpg',
            'kategori'      => 'required|string|max:255',
            'lead_time'     => 'required|integer|min:0',
            'satuans.*.id' => 'nullable|exists:satuans,id', // ID satuan untuk update
            'satuans.*.nama_satuan' => 'required|string|max:100',
            'satuans.*.level' => 'required|integer|between:1,5',
            'satuans.*.konversi' => 'required|numeric|min:0.0001',
            'satuans.*.harga_toko_kecil' => 'nullable|numeric|min:0',
            'satuans.*.harga_individu' => 'nullable|numeric|min:0',
        ];

        $request->validate($rules);

        $produk = Produk::findOrFail($id);

        DB::beginTransaction();
        try {
            // Handle gambar
            $fileName = $produk->gambar;
            if ($request->hasFile('gambar')) {
                // Hapus gambar lama
                if ($fileName && Storage::disk('public')->exists('gambar_produk/' . $fileName)) {
                    Storage::disk('public')->delete('gambar_produk/' . $fileName);
                }
                $gambar = $request->file('gambar');
                $fileName = 'produk_' . Str::slug($request->nama_produk) . '_' . time() . '.' . $gambar->getClientOriginalExtension();
                $gambar->storeAs('gambar_produk', $fileName, 'public');
            }

            // Update produk dasar
            $produk->update([
                'nama_produk'   => $request->nama_produk,
                'deskripsi'     => $request->deskripsi,
                'kategori'      => $request->kategori,
                'gambar'        => $fileName,
                'lead_time'     => $request->lead_time,
                'daily_usage'   => $request->daily_usage, // Atau pertahankan nilai lama jika tidak diubah
                'safety_stock'  => $request->safety_stock, // Atau pertahankan nilai lama
            ]);

            // Tangani pembaruan satuan dan harga
            $submittedSatuanIds = collect($request->satuans)->pluck('id')->filter()->toArray();

            // Hapus satuan dan harga yang tidak dikirim lagi dari form
            $produk->satuans()->whereNotIn('id', $submittedSatuanIds)->delete();

            foreach ($request->satuans as $satuanData) {
                // Cari atau buat satuan baru
                $satuan = $produk->satuans()->updateOrCreate(
                    ['id' => $satuanData['id'] ?? null],
                    [
                        'nama_satuan' => $satuanData['nama_satuan'],
                        'level' => $satuanData['level'],
                        'konversi_ke_satuan_utama' => $satuanData['konversi'],
                    ]
                );

                // Hapus harga lama untuk satuan ini, lalu simpan yang baru
                $satuan->hargaProduk()->delete();
                if (isset($satuanData['harga_toko_kecil'])) {
                    $satuan->hargaProduk()->create([
                        'produk_id' => $produk->id,
                        'jenis_pelanggan' => 'Toko Kecil',
                        'harga' => $satuanData['harga_toko_kecil'],
                    ]);
                }
                if (isset($satuanData['harga_individu'])) {
                    $satuan->hargaProduk()->create([
                        'produk_id' => $produk->id,
                        'jenis_pelanggan' => 'Individu',
                        'harga' => $satuanData['harga_individu'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('produk.index')->with('success', 'Data produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Produk Update Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
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


            return redirect()->route('produk.index')->with('success', 'Data produk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('produk.index')->with('error', 'Gagal menghapus data produk. ' . $e->getMessage());
        }
    }
}
