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
        // Eager load relasi satuans dan hargaProduks (beserta relasi satuan)
        $produk = Produk::with(['satuans', 'hargaProduks.satuan'])->get();

        // Filter produk dengan stok <= ROP
        $produkMenipis = $produk->filter(fn($item) => $item->isStokDiBawahROP());

        return view('produk.index', compact('produk', 'produkMenipis'));
    }

    public function create()
    {
        $satuans = \App\Models\Satuan::all();  // ganti nama variabel jadi $satuans supaya sesuai dengan di view
        return view('produk.create', compact('satuans'));
    }

    public function store(Request $request)
    {
        Log::info('Request Produk Store:', $request->all());

        // Bersihkan input stok_bertahap jika mode_stok bertahap
        if ($request->mode_stok === 'bertahap') {
            $stokBertahapBersih = collect($request->stok_bertahap)->filter(function ($item) {
                return !empty($item['satuan_id']) && floatval($item['qty']) > 0;
            })->values()->all();
            $request->merge(['stok_bertahap' => $stokBertahapBersih]);

            // Bersihkan juga input safety_stock_bertahap
            $safetyStockBertahapBersih = collect($request->safety_stock_bertahap)->filter(function ($item) {
                return !empty($item['satuan_id']) && floatval($item['qty']) > 0;
            })->values()->all();
            $request->merge(['safety_stock_bertahap' => $safetyStockBertahapBersih]);
        } else {
            $request->merge([
                'stok_bertahap' => [],
                'safety_stock_bertahap' => [],
            ]);
        }

        // Atur rules validasi
        $rules = [
            'nama_produk'    => 'required|string|max:255',
            'deskripsi'      => 'required|string|max:500',
            'gambar'         => 'required|image|mimes:jpeg,png,jpg',
            'kategori'       => 'required|string|max:255',
            'lead_time'      => 'required|integer|min:0',
            'mode_stok'      => 'required|in:utama,bertahap',
        ];

        if ($request->mode_stok === 'utama') {
            $rules['stok'] = 'required|numeric|min:0';
            $rules['safety_stock'] = 'required|numeric|min:0';
        } else {
            $rules['stok_bertahap'] = 'required|array|min:1';
            foreach ($request->stok_bertahap as $index => $item) {
                $rules["stok_bertahap.$index.satuan_id"] = 'required|exists:satuans,id';
                $rules["stok_bertahap.$index.qty"] = 'required|numeric|min:0.01';
            }

            $rules['safety_stock_bertahap'] = 'required|array|min:1';
            foreach ($request->safety_stock_bertahap as $index => $item) {
                $rules["safety_stock_bertahap.$index.satuan_id"] = 'required|exists:satuans,id';
                $rules["safety_stock_bertahap.$index.qty"] = 'required|numeric|min:0.01';
            }
        }

        // Validasi
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Validasi file gambar
        if (!$request->hasFile('gambar') || !$request->file('gambar')->isValid()) {
            return back()->with('error', 'File gambar tidak valid.');
        }

        $gambar = $request->file('gambar');
        $slugNama = Str::slug($request->nama_produk) ?: 'produk';
        $fileName = 'produk_' . $slugNama . '_' . time() . '.' . $gambar->getClientOriginalExtension();

        try {
            DB::beginTransaction();

            $gambar->storeAs('gambar_produk', $fileName, 'public');

            // Hitung stok final
            $stokFinal = 0;
            if ($request->mode_stok === 'utama') {
                $stokFinal = $request->stok ?? 0;
            } else {
                $satuanIds = collect($request->stok_bertahap)->pluck('satuan_id')->all();
                $satuans = \App\Models\Satuan::whereIn('id', $satuanIds)->get()->keyBy('id');

                foreach ($request->stok_bertahap as $item) {
                    $satuanId = $item['satuan_id'];
                    $qty = (float) $item['qty'];
                    $konversi = $satuans[$satuanId]->konversi_ke_satuan_utama ?? 1;
                    $stokFinal += $qty * $konversi;
                }
            }

            // Hitung safety stock final
            $safetyStockFinal = 0;
            if ($request->mode_stok === 'utama') {
                $safetyStockFinal = $request->safety_stock ?? 0;
            } else {
                $satuanIdsSafety = collect($request->safety_stock_bertahap)->pluck('satuan_id')->all();
                $satuansSafety = \App\Models\Satuan::whereIn('id', $satuanIdsSafety)->get()->keyBy('id');

                foreach ($request->safety_stock_bertahap as $item) {
                    $satuanId = $item['satuan_id'];
                    $qty = (float) $item['qty'];
                    $konversi = $satuansSafety[$satuanId]->konversi_ke_satuan_utama ?? 1;
                    $safetyStockFinal += $qty * $konversi;
                }
            }

            // Simpan produk
            $produk = Produk::create([
                'nama_produk'   => $request->nama_produk,
                'deskripsi'     => $request->deskripsi,
                'stok'          => $stokFinal,
                'kategori'      => $request->kategori,
                'gambar'        => $fileName,
                'lead_time'     => $request->lead_time,
                'daily_usage'   => 0,
                'safety_stock'  => $safetyStockFinal,
            ]);

            DB::commit();
            Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('produk.index', $produk->id)
                ->with('success', 'Produk berhasil disimpan. Silakan tambahkan harga.');
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

        // Dekonstruksi stok
        $stokSisa = (int) $produk->stok;
        $stokBertingkatDefault = [];

        foreach ($satuanBertingkat as $satuan) {
            if ($satuan->konversi_ke_satuan_utama <= 0) continue;
            $jumlah = intdiv($stokSisa, $satuan->konversi_ke_satuan_utama);
            $stokSisa %= $satuan->konversi_ke_satuan_utama;
            $stokBertingkatDefault[$satuan->id] = $jumlah;
        }
        $stokBertingkatDefault['utama'] = $stokSisa;

        // Dekonstruksi safety stock
        $safetySisa = (int) $produk->safety_stock;
        $safetyStockBertingkatDefault = [];

        foreach ($satuanBertingkat as $satuan) {
            if ($satuan->konversi_ke_satuan_utama <= 0) continue;
            $jumlah = intdiv($safetySisa, $satuan->konversi_ke_satuan_utama);
            $safetySisa %= $satuan->konversi_ke_satuan_utama;
            $safetyStockBertingkatDefault[$satuan->id] = $jumlah;
        }
        $safetyStockBertingkatDefault['utama'] = $safetySisa;

        return view('produk.edit', compact(
            'produk',
            'satuanBertingkat',
            'stokBertingkatDefault',
            'safetyStockBertingkatDefault'
        ));
    }

    public function update(Request $request, $id)
    {
        Log::info('Request Produk Update:', $request->all());

        // Bersihkan input kosong dari stok_bertahap dan safety_stock_bertahap
        if ($request->mode_stok === 'bertahap') {
            $stokBersih = collect($request->stok_bertahap)->filter(function ($qty) {
                return is_numeric($qty) && floatval($qty) > 0;
            })->toArray();

            $safetyBersih = collect($request->safety_stock_bertahap)->filter(function ($qty) {
                return is_numeric($qty) && floatval($qty) > 0;
            })->toArray();

            $request->merge([
                'stok_bertahap' => $stokBersih,
                'safety_stock_bertahap' => $safetyBersih
            ]);
        }

        // Aturan validasi
        $rules = [
            'nama_produk'    => 'required|string|max:255',
            'deskripsi'      => 'required|string|max:500',
            'gambar'         => 'nullable|image|mimes:jpeg,png,jpg',
            'kategori'       => 'required|string|max:255',
            'lead_time'      => 'required|integer|min:0',
            'daily_usage'    => 'required|numeric|min:0',
            'mode_stok'      => 'required|in:utama,bertahap',
        ];

        if ($request->mode_stok === 'utama') {
            $rules['stok'] = 'required|numeric|min:0';
            $rules['safety_stock'] = 'required|numeric|min:0';
        } else {
            $rules['stok'] = 'nullable';
            $rules['stok_bertahap'] = 'required|array|min:1';
            $rules['stok_bertahap.*'] = 'numeric|min:0.01';

            $rules['safety_stock'] = 'nullable';
            $rules['safety_stock_bertahap'] = 'required|array|min:1';
            $rules['safety_stock_bertahap.*'] = 'numeric|min:0.01';
        }

        // Validasi request
        $request->validate($rules);

        $produk = Produk::findOrFail($id);

        try {
            DB::beginTransaction();

            // Handle gambar
            $fileName = $produk->gambar;
            if ($request->hasFile('gambar')) {
                $gambar = $request->file('gambar');
                $fileName = 'produk_' . Str::slug($request->nama_produk) . '_' . time() . '.' . $gambar->getClientOriginalExtension();
                $gambar->storeAs('gambar_produk', $fileName, 'public');
            }

            // Hitung stok akhir
            $stokUpdate = $request->stok;
            if ($request->mode_stok === 'bertahap') {
                $stokUpdate = 0;
                foreach ($request->stok_bertahap as $key => $qty) {
                    $qty = floatval($qty ?? 0);
                    $konversi = 1;
                    if ($key !== 'utama') {
                        $satuan = $produk->satuans()->where('id', $key)->first();
                        $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    }
                    $stokUpdate += $qty * $konversi;
                }
            }

            // Hitung safety stock akhir
            $safetyUpdate = $request->safety_stock;
            if ($request->mode_stok === 'bertahap') {
                $safetyUpdate = 0;
                foreach ($request->safety_stock_bertahap as $key => $qty) {
                    $qty = floatval($qty ?? 0);
                    $konversi = 1;
                    if ($key !== 'utama') {
                        $satuan = $produk->satuans()->where('id', $key)->first();
                        $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    }
                    $safetyUpdate += $qty * $konversi;
                }
            }

            // Update produk
            $produk->update([
                'nama_produk'   => $request->nama_produk,
                'deskripsi'     => $request->deskripsi,
                'stok'          => $stokUpdate,
                'kategori'      => $request->kategori,
                'gambar'        => $fileName,
                'lead_time'     => $request->lead_time,
                'daily_usage'   => $request->daily_usage,
                'safety_stock'  => $safetyUpdate,
            ]);

            DB::commit();
            Artisan::call('produk:update-dailyusage-rop');

            return redirect()->route('produk.index')->with('success', 'Data produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Produk Update Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

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
