<?php

namespace App\Http\Controllers;

use App\Models\TransaksiOnline;
use App\Models\TransaksiOnlineDetail;
use App\Models\Produk;
use App\Models\Satuan;
use App\Models\HargaProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;


class TransaksiOnlineController extends Controller
{
    public function index()
    {
        $transaksis = TransaksiOnline::with('user')->latest()->get();
        return view('transaksi_online.index', compact('transaksis'));
    }

    public function create()
    {

        $users = \App\Models\User::where('role', 'pelanggan')->get();

        // Build daftar produk, satuan, harga dalam bentuk array siap pakai
        $produks = \App\Models\Produk::with(['satuans', 'hargaProduks'])->get();

        $produkOptions = $produks->map(function ($produk) {
            return [
                'id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'satuans' => $produk->satuans->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'nama_satuan' => $s->nama_satuan,
                    ];
                })->values(),
                'harga' => $produk->hargaProduks
                    ->groupBy('satuan_id')
                    ->mapWithKeys(function ($h) {
                        $satuanId = $h->first()->satuan_id;
                        $individu = optional($h->firstWhere('jenis_pelanggan', 'Individu'))->harga;
                        $toko_kecil = optional($h->firstWhere('jenis_pelanggan', 'Toko Kecil'))->harga;
                        return [
                            $satuanId => [
                                "Individu" => $individu,
                                "Toko Kecil" => $toko_kecil,
                            ]
                        ];
                    }),
            ];
        })->values();

        return view('transaksi_online.create', [
            'users' => $users,
            'produkOptions' => $produkOptions,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'status_pembayaran' => 'required|in:pending,lunas,gagal',
            'status_transaksi' => 'required|in:diproses,diantar,diambil,selesai,batal',
            'produk_id.*' => 'required|exists:produks,id',
            'jumlah_json.*' => 'required|string', // JSON dari inputan
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            $randomOnline = strtoupper(Str::random(4));
            $kode = 'TX-ON-' . now()->format('ymd') . '-' . $randomOnline;

            // Cari data pelanggan dan jenis_pelanggan
            $user = \App\Models\User::findOrFail($request->user_id);
            $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

            $transaksi = TransaksiOnline::create([
                'user_id' => $request->user_id,
                'kode_transaksi' => $kode,
                'tanggal' => $request->tanggal,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $request->status_pembayaran,
                'status_transaksi' => $request->status_transaksi,
                'catatan' => $request->catatan,
                'diambil_di_toko' => $request->diambil_di_toko ?? false,
                'alamat_pengambilan' => $request->alamat_pengambilan,
                'total' => 0,
            ]);

            foreach ($request->produk_id as $i => $produkId) {
                $jumlahJson = $request->jumlah_json[$i] ?? '{}';
                // Parse jumlah bertingkat
                $jumlahArr = is_array($jumlahJson) ? $jumlahJson : json_decode($jumlahJson, true);
                if (!is_array($jumlahArr) || empty($jumlahArr)) continue;

                $subtotalProduk = 0;
                $produk = Produk::findOrFail($produkId);

                foreach ($jumlahArr as $satuanId => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;
                    $satuan = Satuan::findOrFail($satuanId);

                    // Cari harga sesuai jenis pelanggan
                    $harga = HargaProduk::where('produk_id', $produkId)
                        ->where('satuan_id', $satuanId)
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->value('harga') ?? 0;

                    // Subtotal per satuan
                    $subtotalProduk += $harga * $qty;

                    // Konversi & Update stok produk
                    $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                    $jumlahUtama = $qty * $konversi;
                    if ($produk->stok < $jumlahUtama) {
                        DB::rollBack();
                        return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }
                    $produk->stok -= $jumlahUtama;
                    $produk->save();

                    // Catat log stok
                    \App\Models\Stok::create([
                        'produk_id' => $produkId,
                        'satuan_id' => $satuanId,
                        'jenis' => 'keluar',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Transaksi online #' . $kode,
                    ]);
                }

                // Simpan detail
                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'jumlah_json' => $jumlahArr,
                    // 'subtotal' => $subtotalProduk, // jika tambah kolom subtotal
                ]);

                $total += $subtotalProduk;
            }

            $transaksi->update(['total' => $total]);

            // Simpan keuangan hanya jika status pembayaran lunas
            if ($request->status_pembayaran === 'lunas') {
                \App\Models\Keuangan::create([
                    'transaksi_online_id' => $transaksi->id, // ini harus kolom transaksi_online_id untuk transaksi online
                    'tanggal' => $request->tanggal,
                    'jenis' => 'pemasukan',
                    'nominal' => $total,
                    'keterangan' => 'Pemasukan dari transaksi online #' . $kode,
                    'sumber' => 'online',
                ]);
            }

            Artisan::call('produk:update-dailyusage-rop');
            DB::commit();

            return redirect()->route('transaksi_online.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }





    public function show(TransaksiOnline $transaksiOnline)
    {
        $transaksiOnline->load(['detail.produk']);
        return view('transaksi_online.show', compact('transaksiOnline'));
    }

    public function edit(TransaksiOnline $transaksiOnline)
    {
        $users = \App\Models\User::where('role', 'pelanggan')->get();

        $produks = \App\Models\Produk::with(['satuans', 'hargaProduks'])->get();

        // Bangun produkOptions seperti di method create
        $produkOptions = $produks->map(function ($produk) {
            return [
                'id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'satuans' => $produk->satuans->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'nama_satuan' => $s->nama_satuan,
                    ];
                })->values(),
                'harga' => $produk->hargaProduks
                    ->groupBy('satuan_id')
                    ->mapWithKeys(function ($h) {
                        $satuanId = $h->first()->satuan_id;
                        $individu = optional($h->firstWhere('jenis_pelanggan', 'Individu'))->harga;
                        $toko_kecil = optional($h->firstWhere('jenis_pelanggan', 'Toko Kecil'))->harga;
                        return [
                            $satuanId => [
                                "Individu" => $individu,
                                "Toko Kecil" => $toko_kecil,
                            ]
                        ];
                    }),
            ];
        })->values();

        $transaksiOnline->load(['detail']);

        return view('transaksi_online.edit', compact('transaksiOnline', 'users', 'produkOptions'));
    }


    public function update(Request $request, TransaksiOnline $transaksiOnline)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'status_pembayaran' => 'required|in:pending,lunas,gagal',
            'status_transaksi' => 'required|in:diproses,diantar,diambil,selesai,batal',
            'produk_id.*' => 'required|exists:produks,id',
            'jumlah_json.*' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Rollback stok lama
            foreach ($transaksiOnline->detail as $detail) {
                $produk = $detail->produk;
                $jumlahArr = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);
                if (!$produk || !is_array($jumlahArr)) continue;
                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = Satuan::find($satuanId);
                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    $jumlahUtama = floatval($qty) * $konversi;
                    $produk->stok += $jumlahUtama;
                    $produk->save();
                    \App\Models\Stok::create([
                        'produk_id' => $produk->id,
                        'satuan_id' => $satuan?->id,
                        'jenis' => 'masuk',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Rollback transaksi online #' . $transaksiOnline->kode_transaksi,
                    ]);
                }
            }

            // Update transaksi utama
            $transaksiOnline->update([
                'user_id' => $request->user_id,
                'tanggal' => $request->tanggal,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $request->status_pembayaran,
                'status_transaksi' => $request->status_transaksi,
                'catatan' => $request->catatan,
                'diambil_di_toko' => $request->diambil_di_toko ?? false,
                'alamat_pengambilan' => $request->alamat_pengambilan,
            ]);

            // Hapus detail lama
            $transaksiOnline->detail()->delete();

            // Hapus data keuangan lama yang terkait transaksi ini
            \App\Models\Keuangan::where('transaksi_online_id', $transaksiOnline->id)->delete();

            // Ambil jenis pelanggan baru
            $user = \App\Models\User::findOrFail($request->user_id);
            $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

            $total = 0;

            foreach ($request->produk_id as $i => $produkId) {
                $jumlahJson = $request->jumlah_json[$i] ?? '{}';
                $jumlahArr = is_array($jumlahJson) ? $jumlahJson : json_decode($jumlahJson, true);
                if (!is_array($jumlahArr) || empty($jumlahArr)) continue;

                $subtotalProduk = 0;
                $produk = Produk::findOrFail($produkId);

                foreach ($jumlahArr as $satuanId => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;
                    $satuan = Satuan::findOrFail($satuanId);

                    $harga = HargaProduk::where('produk_id', $produkId)
                        ->where('satuan_id', $satuanId)
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->value('harga') ?? 0;

                    $subtotalProduk += $harga * $qty;

                    $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                    $jumlahUtama = $qty * $konversi;
                    if ($produk->stok < $jumlahUtama) {
                        DB::rollBack();
                        return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }
                    $produk->stok -= $jumlahUtama;
                    $produk->save();

                    \App\Models\Stok::create([
                        'produk_id' => $produkId,
                        'satuan_id' => $satuanId,
                        'jenis' => 'keluar',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Update transaksi online #' . $transaksiOnline->kode_transaksi,
                    ]);
                }

                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksiOnline->id,
                    'produk_id' => $produkId,
                    'jumlah_json' => $jumlahArr,
                ]);

                $total += $subtotalProduk;
            }

            $transaksiOnline->update(['total' => $total]);

            // Simpan data keuangan baru jika status pembayaran lunas
            if ($request->status_pembayaran === 'lunas') {
                \App\Models\Keuangan::create([
                    'transaksi_online_id' => $transaksiOnline->id, // Ganti dari 'transaksi_id' ke 'transaksi_online_id'
                    'tanggal' => $request->tanggal,
                    'jenis' => 'pemasukan',
                    'nominal' => $total,
                    'keterangan' => 'Pemasukan dari transaksi online #' . $transaksiOnline->kode_transaksi,
                    'sumber' => 'online',
                ]);
            }

            Artisan::call('produk:update-dailyusage-rop');
            DB::commit();

            return redirect()->route('transaksi_online.index')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }


    public function destroy(TransaksiOnline $transaksiOnline)
    {
        DB::beginTransaction();
        try {
            foreach ($transaksiOnline->detail as $detail) {
                $produk = $detail->produk;
                $jumlahArr = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);
                if (!$produk || !is_array($jumlahArr)) continue;
                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = Satuan::find($satuanId);
                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    $jumlahUtama = floatval($qty) * $konversi;
                    $produk->stok += $jumlahUtama;
                    $produk->save();
                    \App\Models\Stok::create([
                        'produk_id' => $detail->produk_id,
                        'satuan_id' => $satuan?->id,
                        'jenis' => 'masuk',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Transaksi online dihapus (#' . $transaksiOnline->kode_transaksi . ')',
                    ]);
                }
            }

            $transaksiOnline->detail()->delete();

            // Hapus catatan keuangan yang terkait dengan transaksi online ini
            \App\Models\Keuangan::where('transaksi_online_id', $transaksiOnline->id)->delete();

            // Hapus transaksi online
            $transaksiOnline->delete();

            Artisan::call('produk:update-dailyusage-rop');

            DB::commit();

            return redirect()->route('transaksi_online.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
