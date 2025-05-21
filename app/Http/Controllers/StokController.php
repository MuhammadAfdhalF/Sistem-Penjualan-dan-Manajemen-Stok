<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use App\Models\Produk;  // Pastikan model Produk di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\Satuan;



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
        $produk = Produk::all();

        $produkPertama = $produk->first(); // ambil produk pertama

        $satuanBertingkat = collect();
        $stokBertingkatDefault = [];

        if ($produkPertama) {
            $satuanBertingkat = $produkPertama->satuans()->orderByDesc('konversi_ke_satuan_utama')->get();

            foreach ($satuanBertingkat as $satuan) {
                $stokBertingkatDefault[$satuan->id] = 0;
            }
            $stokBertingkatDefault['utama'] = 0;
        }

        return view('stok.create', compact('produk', 'satuanBertingkat', 'stokBertingkatDefault', 'produkPertama'));
    }




    public function store(Request $request)
    {
        Log::info('Request Produk Store:', $request->all());

        // Proses stok bertingkat dulu jika ada (opsional, sesuaikan jika kamu pakai stok_bertahap)
        if ($request->has('stok_bertahap')) {
            $stokBertahap = $request->input('stok_bertahap');
            $jumlah = 0;

            foreach ($stokBertahap as $satuanId => $qty) {
                $qty = (float)$qty;
                if ($qty <= 0) continue;

                if ($satuanId === 'utama') {
                    $konversi = 1;
                } else {
                    $satuan = Satuan::find($satuanId);
                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                }

                $jumlah += $qty * $konversi;
            }

            $request->merge([
                'jumlah' => $jumlah,
                'satuan_id' => null,
            ]);
        }

        $request->validate([
            'produk_id'  => 'required|exists:produks,id',
            'jenis'      => 'required|in:masuk,keluar',
            'jumlah'     => 'required|numeric|min:0.01',
            'satuan_id'  => 'nullable|exists:satuans,id',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            Log::info('Store Stok - mulai proses', ['request' => $request->all()]);

            $produk = Produk::findOrFail($request->produk_id);
            Log::info('Produk ditemukan', ['produk_id' => $produk->id, 'stok_sebelum' => $produk->stok]);

            $konversi = 1; // default satuan utama
            if ($request->satuan_id) {
                $satuan = Satuan::find($request->satuan_id);
                if ($satuan) {
                    $konversi = $satuan->konversi_ke_satuan_utama;
                    Log::info('Satuan ditemukan', ['satuan_id' => $satuan->id, 'konversi' => $konversi]);
                } else {
                    Log::warning('Satuan tidak ditemukan walaupun ada satuan_id', ['satuan_id' => $request->satuan_id]);
                }
            }

            $jumlah_utama = $request->jumlah * $konversi;
            Log::info('Jumlah utama dihitung', ['jumlah_input' => $request->jumlah, 'jumlah_utama' => $jumlah_utama]);

            $stok = Stok::create([
                'produk_id'  => $request->produk_id,
                'jenis'      => $request->jenis,
                'jumlah'     => $jumlah_utama,
                'satuan_id'  => $request->satuan_id,
                'keterangan' => $request->keterangan,
            ]);
            Log::info('Data stok berhasil dibuat', ['stok_id' => $stok->id]);

            if ($request->jenis == 'masuk') {
                $produk->stok += $jumlah_utama;
            } elseif ($request->jenis == 'keluar') {
                $produk->stok -= $jumlah_utama;
            }
            $produk->save();
            Log::info('Stok produk diperbarui', ['produk_id' => $produk->id, 'stok_sesudah' => $produk->stok]);

            DB::commit();

            Artisan::call('produk:update-dailyusage-rop');

            Log::info('Store Stok - selesai proses');
            return redirect()->route('stok.index')->with('success', 'Data stok berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Stok - gagal menyimpan data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data stok: ' . $e->getMessage());
        }
    }


    public function show(Stok $stok)
    {
        //
    }


    public function edit($id)
    {

        // Cari data stok yang diedit
        $stok = Stok::findOrFail($id);

        // Ambil semua produk (untuk dropdown produk)
        $produk = Produk::all();

        // Ambil satuan bertingkat dari produk terkait stok ini
        $satuanBertingkat = \App\Models\Satuan::where('produk_id', $stok->produk_id)
            ->where('konversi_ke_satuan_utama', '>', 1)
            ->get();

        // Siapkan stok bertahap default: nilai stok per satuan
        // Bisa ambil dari database stok bertingkat terkait atau default 0
        $stokBertingkatDefault = [];

        // Misal kalau kamu simpan stok bertahap di tabel lain atau
        // buat logika ambil stok bertahap terkait stok ini
        // Kalau belum ada, bisa default 0 semua
        foreach ($satuanBertingkat as $satuan) {
            $stokBertingkatDefault[$satuan->id] = 0;
        }
        $stokBertingkatDefault['utama'] = 0;

        // Jika mau, bisa isi stokBertingkatDefault['utama'] dari stok utama produk, atau dari stok yang diedit

        return view('stok.edit', compact('stok', 'produk', 'satuanBertingkat', 'stokBertingkatDefault'));
    }


    public function update(Request $request, Stok $stok)
    {
        Log::info('Request Produk update:', $request->all());

        $request->validate([
            'produk_id'  => 'required|exists:produks,id',
            'jenis'      => 'required|in:masuk,keluar',
            'keterangan' => 'nullable|string',
            // Tidak lagi validasi 'jumlah' karena kita hitung dari stok_bertahap
        ]);

        DB::beginTransaction();
        try {
            $produk = Produk::findOrFail($request->produk_id);

            // Batalkan efek stok sebelumnya
            if ($stok->jenis === 'masuk') {
                $produk->stok -= $stok->jumlah;
            } elseif ($stok->jenis === 'keluar') {
                $produk->stok += $stok->jumlah;
            }

            // Hitung jumlah baru dari stok_bertahap
            $jumlah_utama = 0;
            foreach ($request->stok_bertahap ?? [] as $satuanId => $jumlah) {
                $jumlah = intval($jumlah);
                if ($satuanId === 'utama') {
                    $konversi = 1;
                } else {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                }
                $jumlah_utama += $jumlah * $konversi;
            }

            Log::info('Jumlah utama hasil konversi', ['jumlah_utama' => $jumlah_utama]);

            // Update data stok
            $stok->update([
                'produk_id'  => $request->produk_id,
                'jenis'      => $request->jenis,
                'jumlah'     => $jumlah_utama,
                'keterangan' => $request->keterangan,
            ]);

            // Terapkan efek stok baru
            if ($request->jenis === 'masuk') {
                $produk->stok += $jumlah_utama;
            } elseif ($request->jenis === 'keluar') {
                $produk->stok -= $jumlah_utama;
            }

            $produk->save();
            DB::commit();

            Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('stok.index')->with('success', 'Data stok berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update stok', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data stok. ' . $e->getMessage());
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
