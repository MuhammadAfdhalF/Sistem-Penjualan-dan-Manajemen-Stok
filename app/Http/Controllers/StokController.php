<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use App\Models\Produk;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class StokController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Stok::with(['produk', 'satuan'])
            ->latest(); // sama dengan orderBy('created_at', 'desc')

        // Filter tanggal (format: yyyy-mm-dd)
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter bulan & tahun
        if ($request->month) {
            $query->whereMonth('created_at', $request->month);
        }
        if ($request->year) {
            $query->whereYear('created_at', $request->year);
        }

        // Filter nama produk (produk_id)
        if ($request->produk_id) {
            $query->where('produk_id', $request->produk_id);
        }

        // Filter jenis masuk/keluar
        if ($request->jenis) {
            $query->where('jenis', $request->jenis);
        }

        // Ambil data stok
        $stok = $query->get();

        // Untuk dropdown produk dan tahun
        $daftarProduk = \App\Models\Produk::select('id', 'nama_produk')
            ->orderBy('nama_produk')
            ->get();

        $tahunTersedia = \App\Models\Stok::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->pluck('tahun')
            ->toArray();

        return view('stok.index', compact('stok', 'daftarProduk', 'tahunTersedia'));
    }

    public function create()
    {
        $produk = Produk::all();
        $produkPertama = $produk->first();

        $satuanBertingkat = collect();
        $stokBertingkatDefault = [];

        if ($produkPertama) {
            $satuanBertingkat = $produkPertama->satuans()->orderByDesc('konversi_ke_satuan_utama')->get();
            foreach ($satuanBertingkat as $satuan) {
                $stokBertingkatDefault[$satuan->id] = 0;
            }
        }

        return view('stok.create', compact('produk', 'satuanBertingkat', 'stokBertingkatDefault', 'produkPertama'));
    }

    public function store(Request $request)
    {
        Log::info('Request Produk Store:', $request->all());

        if ($request->has('stok_bertahap')) {
            $stokBertahap = $request->input('stok_bertahap');
            $jumlah = 0;

            foreach ($stokBertahap as $satuanId => $qty) {
                $qty = (float)$qty;
                if ($qty <= 0) continue;

                $satuan = Satuan::find($satuanId);
                $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
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
            $produk = Produk::findOrFail($request->produk_id);
            $konversi = 1;

            if ($request->satuan_id) {
                $satuan = Satuan::find($request->satuan_id);
                $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
            }

            $jumlah_utama = $request->jumlah * $konversi;

            $stok = Stok::create([
                'produk_id'  => $request->produk_id,
                'jenis'      => $request->jenis,
                'jumlah'     => $jumlah_utama,
                'satuan_id'  => $request->satuan_id,
                'keterangan' => $request->keterangan,
            ]);

            if ($request->jenis == 'masuk') {
                $produk->stok += $jumlah_utama;
            } elseif ($request->jenis == 'keluar') {
                $produk->stok -= $jumlah_utama;
            }
            $produk->save();

            DB::commit();

            return redirect()->route('stok.index')->with('success', 'Data stok berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data stok: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $stok = Stok::findOrFail($id);
        $produk = Produk::all();

        // Ambil semua satuan berdasarkan produk yang dipilih, urut dari level tinggi ke rendah
        $satuanBertingkat = \App\Models\Satuan::where('produk_id', $stok->produk_id)
            ->orderByDesc('level') // pastikan urutan level tertinggi ke rendah
            ->get();

        // Hitung jumlah bertingkat dari total stok
        $stokBertingkatDefault = [];
        $sisaJumlah = (int) $stok->jumlah;

        foreach ($satuanBertingkat as $satuan) {
            $konversi = $satuan->konversi_ke_satuan_utama;
            $jumlah = $konversi > 0 ? intdiv($sisaJumlah, $konversi) : 0;
            $stokBertingkatDefault[$satuan->id] = $jumlah;
            $sisaJumlah -= $jumlah * $konversi;
        }

        return view('stok.edit', compact('stok', 'produk', 'satuanBertingkat', 'stokBertingkatDefault'));
    }



    public function update(Request $request, Stok $stok)
    {
        Log::info('Request Produk update:', $request->all());

        $request->validate([
            'produk_id'  => 'required|exists:produks,id',
            'jenis'      => 'required|in:masuk,keluar',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $produk = Produk::findOrFail($request->produk_id);

            if ($stok->jenis === 'masuk') {
                $produk->stok -= $stok->jumlah;
            } elseif ($stok->jenis === 'keluar') {
                $produk->stok += $stok->jumlah;
            }

            $jumlah_utama = 0;
            foreach ($request->stok_bertahap ?? [] as $satuanId => $jumlah) {
                $jumlah = intval($jumlah);
                $satuan = Satuan::find($satuanId);
                $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                $jumlah_utama += $jumlah * $konversi;
            }

            $stok->update([
                'produk_id'  => $request->produk_id,
                'jenis'      => $request->jenis,
                'jumlah'     => $jumlah_utama,
                'keterangan' => $request->keterangan,
            ]);

            if ($request->jenis === 'masuk') {
                $produk->stok += $jumlah_utama;
            } elseif ($request->jenis === 'keluar') {
                $produk->stok -= $jumlah_utama;
            }

            $produk->save();
            DB::commit();


            return redirect()->route('stok.index')->with('success', 'Data stok berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data stok. ' . $e->getMessage());
        }
    }

    public function destroy(Stok $stok)
    {
        try {
            $produk = $stok->produk;

            if ($stok->jenis == 'masuk') {
                $produk->stok -= $stok->jumlah;
            } elseif ($stok->jenis == 'keluar') {
                $produk->stok += $stok->jumlah;
            }

            $produk->save();
            $stok->delete();

            DB::commit();

            return redirect()->route('stok.index')->with('success', 'Data stok berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('stok.index')->with('error', 'Gagal menghapus data stok. Silakan coba lagi.');
        }
    }

    public function createInitialStok($produk_id)
    {
        $produk = Produk::with('satuans')->findOrFail($produk_id);
        $satuans = $produk->satuans;

        return view('stok.create-initial', compact('produk', 'satuans'));
    }

    public function storeInitialStok(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'stok_awal.*.jumlah' => 'nullable|numeric|min:0',
            'stok_awal.*.satuan_id' => 'required|exists:satuans,id',
        ]);

        DB::beginTransaction();
        try {
            $produk = Produk::findOrFail($request->produk_id);
            $totalStokAwal = 0;
            $keteranganGabungan = [];

            // Hitung total stok dan buat string keterangan gabungan
            foreach ($request->stok_awal as $stokData) {
                $jumlah = floatval($stokData['jumlah'] ?? 0);
                if ($jumlah > 0) {
                    $satuan = Satuan::findOrFail($stokData['satuan_id']);
                    $konversi = $satuan->konversi_ke_satuan_utama;

                    $totalStokAwal += $jumlah * $konversi;

                    $keteranganGabungan[] = $jumlah . ' ' . $satuan->nama_satuan;
                }
            }
            // Gunakan implode untuk menggabungkan keterangan, pastikan tidak kosong
            $keteranganAkhir = 'Stok awal produk baru masuk ' ;


            // Buat satu entri stok untuk transaksi gabungan
            if ($totalStokAwal >= 0) { // Gunakan >= 0 agar stok 0 juga tercatat jika memang itu tujuannya
                Stok::create([
                    'produk_id' => $produk->id,
                    'jenis' => 'masuk',
                    'jumlah' => $totalStokAwal,
                    'keterangan' => $keteranganAkhir,
                    'satuan_id' => null, // Tidak ada satu satuan spesifik
                ]);
            }

            // Perbarui total stok produk
            $produk->stok = $totalStokAwal;
            $produk->save();

            DB::commit();

            return redirect()->route('produk.index')->with('success', 'Stok awal produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan stok awal: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data. Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
