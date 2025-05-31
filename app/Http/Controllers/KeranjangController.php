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
    public function index(Request $request)
    {
        $user = Auth::user();
        $jenis = $user->jenis_pelanggan ?? 'Individu';

        if ($user->role === 'admin') {
            $keranjangQuery = Keranjang::with(['user', 'produk.satuans', 'produk.hargaProduks']);

            // Filter tanggal
            if ($request->date) {
                $keranjangQuery->whereDate('created_at', $request->date);
            }
            // Filter bulan
            if ($request->month) {
                $keranjangQuery->whereMonth('created_at', $request->month);
            }
            // Filter tahun
            if ($request->year) {
                $keranjangQuery->whereYear('created_at', $request->year);
            }
            // Filter nama pelanggan
            if ($request->user_id) {
                $keranjangQuery->where('user_id', $request->user_id);
            }

            $keranjangs = $keranjangQuery->latest()->get();

            // Untuk dropdown pelanggan dan tahun
            $daftarPelanggan = \App\Models\User::where('role', 'pelanggan')->orderBy('nama')->get();
            $tahunTersedia = Keranjang::selectRaw('YEAR(created_at) as tahun')->distinct()->pluck('tahun')->toArray();

            return view('keranjang.keranjang_admin.index', compact('keranjangs', 'daftarPelanggan', 'tahunTersedia'));
        } else {
            $keranjangs = Keranjang::with(['produk.satuans', 'produk.hargaProduks'])
                ->where('user_id', $user->id)->get();
            return view('keranjang.keranjang_pelanggan.index', compact('keranjangs', 'jenis'));
        }
    }


    public function create()
    {
        $user = Auth::user();
        $jenis = $user->jenis_pelanggan ?? 'Individu';
        $produks = \App\Models\Produk::with([
            'satuans',
            'hargaProduks' => function ($q) use ($jenis) {
                $q->where('jenis_pelanggan', $jenis);
            }
        ])->get();
        return view('keranjang.keranjang_pelanggan.create', compact('produks', 'jenis'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->back()->with('error', 'Admin tidak dapat menambah keranjang.');
        }

        $produk_ids = $request->input('produk_id', []);
        $jumlah_jsons = $request->input('jumlah_json', []);

        // Log awal semua request (cek type)
        \Log::info('[KERANJANG] store input', [
            'produk_ids'    => $produk_ids,
            'jumlah_jsons'  => $jumlah_jsons,
            'request_all'   => $request->all(),
        ]);

        DB::beginTransaction();
        try {
            foreach ($produk_ids as $i => $produk_id) {
                $jumlah_json = $jumlah_jsons[$i] ?? null;
                \Log::info('[KERANJANG] Loop produk', [
                    'index'             => $i,
                    'produk_id'         => $produk_id,
                    'jumlah_json_raw'   => $jumlah_json,
                ]);
                if (!$produk_id || !$jumlah_json) continue;

                // Pastikan $jumlah_json selalu array associative satuan_id => qty
                $daftarJumlah = is_array($jumlah_json)
                    ? $jumlah_json
                    : json_decode($jumlah_json, true);

                // Safety jika decode gagal
                if (!is_array($daftarJumlah)) $daftarJumlah = [];
                \Log::info('[KERANJANG] jumlah_json parsed', [
                    'produk_id'   => $produk_id,
                    'daftarJumlah' => $daftarJumlah,
                ]);

                if (empty($daftarJumlah)) continue;

                // Gabungkan jumlah per satuan jika sudah ada di keranjang
                $keranjang = \App\Models\Keranjang::where('user_id', $user->id)
                    ->where('produk_id', $produk_id)
                    ->first();

                if ($keranjang) {
                    $existing = $keranjang->jumlah_json;
                    if (is_string($existing)) $existing = json_decode($existing, true);
                    if (!is_array($existing)) $existing = [];

                    foreach ($daftarJumlah as $satuan_id => $qty) {
                        $qty = floatval($qty);
                        if (isset($existing[$satuan_id])) {
                            $existing[$satuan_id] += $qty;
                        } else {
                            $existing[$satuan_id] = $qty;
                        }
                    }
                    $keranjang->jumlah_json = $existing;
                    $keranjang->save();
                    \Log::info('[KERANJANG] Keranjang updated', [
                        'produk_id' => $produk_id,
                        'jumlah_json_final' => $keranjang->jumlah_json,
                    ]);
                } else {
                    $baru = Keranjang::create([
                        'user_id' => $user->id,
                        'produk_id' => $produk_id,
                        'jumlah_json' => $daftarJumlah,
                    ]);
                    \Log::info('[KERANJANG] Keranjang created', [
                        'produk_id' => $produk_id,
                        'jumlah_json_final' => $baru->jumlah_json,
                    ]);
                }
            }
            DB::commit();

            return redirect()->route('keranjang.index')->with('success', 'Item berhasil ditambahkan ke keranjang.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('[KERANJANG] ERROR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
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

        // Validasi: harus array atau bisa string json (biar lebih fleksibel)
        $input = $request->input('jumlah_json');
        if (is_string($input)) {
            $jumlahJson = json_decode($input, true);
        } else {
            $jumlahJson = $input;
        }
        // Filter & pastikan associative array, semua key satuan_id => jumlah
        if (!is_array($jumlahJson)) {
            return redirect()->back()->with('error', 'Format jumlah tidak valid.');
        }
        // Hilangkan yang jumlah <= 0
        $jumlahJson = collect($jumlahJson)
            ->filter(function ($qty) {
                return is_numeric($qty) && $qty > 0;
            })
            ->toArray();

        $keranjang->jumlah_json = $jumlahJson;
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
