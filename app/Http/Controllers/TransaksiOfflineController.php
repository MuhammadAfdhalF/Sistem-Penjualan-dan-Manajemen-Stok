<?php

namespace App\Http\Controllers;

use App\Models\TransaksiOffline;
use App\Models\TransaksiOfflineDetail;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Keuangan;
use Illuminate\Support\Facades\Log;
use App\Helpers\MidtransSnap;




class TransaksiOfflineController extends Controller
{

    public function index(Request $request)
    {
        $pelanggans = \App\Models\User::where('role', 'pelanggan')->orderBy('nama')->get();

        $query = \App\Models\TransaksiOffline::with(['detail.produk', 'pelanggan'])->latest();

        // Filter tanggal, bulan, tahun
        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->date);
        }
        if ($request->filled('month')) {
            $query->whereMonth('tanggal', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('tanggal', $request->year);
        }

        // Filter by pelanggan id
        if ($request->filled('pelanggan_id')) {
            $query->where('pelanggan_id', $request->pelanggan_id);
        }

        $transaksi = $query->get();

        return view('transaksi_offline.index', compact('transaksi', 'pelanggans'));
    }

    public function show($id)
    {
        $transaksi = \App\Models\TransaksiOffline::with([
            'pelanggan',
            'detail.produk',  // wajib untuk menampilkan nama produk di view
        ])->findOrFail($id);

        return view('transaksi_offline.show', compact('transaksi'));
    }


    public function create()
    {
        $produk = Produk::with(['satuans', 'hargaProduks'])->get();
        $pelanggans = User::where('role', 'pelanggan')->get();
        $kode_transaksi = 'TX-OFF-' . now()->format('ymd-His') . '-' . strtoupper(Str::random(4));
        $tanggal = now();

        return view('transaksi_offline.create', compact('produk', 'pelanggans', 'kode_transaksi', 'tanggal'));
    }

    public function store(Request $request)
    {
        Log::info('Request input:', $request->all());

        $rules = [
            'kode_transaksi' => 'required|unique:transaksi_offline,kode_transaksi',
            'tanggal' => 'required|date',
            'jenis_pelanggan' => 'required|in:Individu,Toko Kecil',
            'total' => 'required|numeric',
            'pelanggan_id' => 'nullable|exists:users,id',
            'produk_id.*' => 'required|exists:produks,id',
            'jumlah_json.*' => 'required|string',
            'harga_json.*' => 'required|string',
            'metode_pembayaran' => 'required|in:cash,payment_gateway',
        ];

        if ($request->metode_pembayaran === 'cash') {
            $rules['dibayar'] = 'required|numeric';
            $rules['kembalian'] = 'required|numeric';

            $dibayar = floatval(str_replace(['.', ','], ['', '.'], $request->dibayar));
            $total = floatval(str_replace(['.', ','], ['', '.'], $request->total));
            if ($dibayar < $total) {
                return back()->with('error', 'Jumlah dibayar tidak boleh kurang dari total.')->withInput();
            }
        }

        $request->validate($rules);

        $sanitizeMoney = fn($val) => floatval(str_replace(['.', ','], ['', '.'], $val));
        $totalTransaksi = $sanitizeMoney($request->total);
        $kodeTransaksi = $request->kode_transaksi;
        $jenisPelanggan = $request->jenis_pelanggan;
        $pelangganId = $request->pelanggan_id;
        $tanggalTransaksi = $request->tanggal;

        $itemDetailsForMidtrans = [];
        $produkDataRaw = [];

        foreach ($request->produk_id as $i => $produkId) {
            $jumlahArr = json_decode($request->jumlah_json[$i], true);
            $hargaArr = json_decode($request->harga_json[$i], true);
            if (!is_array($jumlahArr)) continue;

            $produk = Produk::findOrFail($produkId);
            $subtotalProduk = 0;

            foreach ($jumlahArr as $satuanId => $qty) {
                if ((int) $qty < 1) continue; // Hindari error Midtrans

                $satuan = \App\Models\Satuan::find($satuanId);
                if (!$satuan) continue;

                $hargaSatuan = $sanitizeMoney($hargaArr[$satuanId] ?? 0);
                $subtotalProduk += $qty * $hargaSatuan;

                $itemDetailsForMidtrans[] = [
                    'id' => $produk->id . '-' . $satuan->id,
                    'price' => (int) $hargaSatuan,
                    'quantity' => (int) $qty,
                    'name' => substr($produk->nama_produk . ' (' . $satuan->nama_satuan . ')', 0, 50), // hindari nama terlalu panjang
                ];
            }

            $produkDataRaw[] = [
                'produk_id' => $produkId,
                'jumlah_json' => $jumlahArr,
                'harga_json' => $hargaArr,
            ];
        }

        // === Payment Gateway ===
        if ($request->metode_pembayaran === 'payment_gateway') {
            $customer = [
                'first_name' => 'Pelanggan Offline',
                'email' => 'offline@example.com',
                'phone' => '081234567890',
            ];

            if ($pelangganId && $userPelanggan = User::find($pelangganId)) {
                $customer['first_name'] = $userPelanggan->nama;
                $customer['email'] = $userPelanggan->email;
                $customer['phone'] = $userPelanggan->no_hp;
            }

            $custom_fields = [
                'custom_field1' => substr($kodeTransaksi, 0, 30),
                'custom_field2' => substr($jenisPelanggan, 0, 30),
                'custom_field3' => substr($tanggalTransaksi, 0, 30),
            ];

            try {
                $snapToken = MidtransSnap::generateSnapToken(
                    $kodeTransaksi,
                    $totalTransaksi,
                    $customer,
                    $itemDetailsForMidtrans,
                    $custom_fields
                );

                return response()->json([
                    'snap_token' => $snapToken,
                    'order_id' => $kodeTransaksi,
                    'total' => $totalTransaksi,
                ]);
            } catch (\Exception $e) {
                Log::error('Gagal generate Snap Token Midtrans: ' . $e->getMessage());
                return response()->json(['error' => 'Gagal memproses pembayaran: ' . $e->getMessage()], 500);
            }
        }

        // === Tunai ===
        try {
            DB::beginTransaction();

            $transaksi = TransaksiOffline::create([
                'kode_transaksi' => $kodeTransaksi,
                'tanggal' => $tanggalTransaksi,
                'jenis_pelanggan' => $jenisPelanggan,
                'total' => $totalTransaksi,
                'dibayar' => $sanitizeMoney($request->dibayar),
                'kembalian' => $sanitizeMoney($request->kembalian),
                'pelanggan_id' => $pelangganId,
                'metode_pembayaran' => 'cash',
                'status_pembayaran' => 'lunas',
            ]);

            foreach ($produkDataRaw as $item) {
                $produkId = $item['produk_id'];
                $jumlahArr = $item['jumlah_json'];
                $hargaArr = $item['harga_json'];

                $produk = Produk::findOrFail($produkId);
                $subtotalProduk = 0;
                $totalJumlahUtama = 0;

                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    if (!$satuan) continue;

                    $hargaSatuan = $hargaArr[$satuanId] ?? 0;
                    $subtotalProduk += $qty * $hargaSatuan;

                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $totalJumlahUtama += $qty * $konversi;
                }

                TransaksiOfflineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'jumlah_json' => $jumlahArr,
                    'harga_json' => $hargaArr,
                    'subtotal' => $subtotalProduk,
                ]);

                if ($produk->stok < $totalJumlahUtama) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                }

                $produk->stok -= $totalJumlahUtama;
                $produk->save();

                Stok::create([
                    'produk_id' => $produkId,
                    'jenis' => 'keluar',
                    'jumlah' => $totalJumlahUtama,
                    'keterangan' => 'Transaksi penjualan offline ' . $transaksi->kode_transaksi,
                ]);
            }

            Keuangan::create([
                'transaksi_id' => $transaksi->id,
                'tanggal' => $tanggalTransaksi,
                'jenis' => 'pemasukan',
                'nominal' => $transaksi->total,
                'keterangan' => 'Pemasukan dari transaksi offline #' . $transaksi->kode_transaksi,
                'sumber' => 'offline',
            ]);

            DB::commit();
            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }




    public function edit($id)
    {
        $transaksi = TransaksiOffline::with('detail.produk', 'detail.produk.satuans')->findOrFail($id);
        $produk = Produk::with('satuans')->get();
        $pelanggans = User::where('role', 'pelanggan')->get();
        return view('transaksi_offline.edit', compact('transaksi', 'produk', 'pelanggans'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'total' => 'required|numeric',
            'dibayar' => 'required|numeric',
            'kembalian' => 'required|numeric',
            'pelanggan_id' => 'nullable|exists:users,id',
            'produk_id.*' => 'required|exists:produks,id',
            'jumlah_json.*' => 'required|string',
            'harga_json.*' => 'required|string',
        ]);

        $sanitizeMoney = fn($val) => floatval(str_replace(['.', ','], ['', '.'], $val));

        \DB::beginTransaction();
        try {
            $transaksi = TransaksiOffline::with('detail')->findOrFail($id);

            // Rollback stok lama dari detail lama
            foreach ($transaksi->detail as $detail) {
                $jumlahArr = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);
                if (!$jumlahArr) $jumlahArr = [];
                $totalJumlahUtama = 0;
                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    if (!$satuan) continue;
                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $totalJumlahUtama += $qty * $konversi;
                }
                $produk = \App\Models\Produk::findOrFail($detail->produk_id);
                $produk->stok += $totalJumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id' => $detail->produk_id,
                    'jenis' => 'masuk',
                    'jumlah' => $totalJumlahUtama,
                    'keterangan' => 'Rollback stok transaksi ' . $transaksi->kode_transaksi,
                ]);
            }

            // Hapus detail lama
            $transaksi->detail()->delete();

            // Update data transaksi utama
            $transaksi->update([
                'tanggal' => $request->tanggal,
                'total' => $sanitizeMoney($request->total),
                'dibayar' => $sanitizeMoney($request->dibayar),
                'kembalian' => $sanitizeMoney($request->kembalian),
                'pelanggan_id' => $request->pelanggan_id ?? null,
            ]);

            // Simpan detail baru
            foreach ($request->produk_id as $i => $produkId) {
                $jumlahArr = json_decode($request->jumlah_json[$i], true);
                $hargaArr = json_decode($request->harga_json[$i], true);

                if (
                    json_last_error() !== JSON_ERROR_NONE ||
                    empty($jumlahArr) || !is_array($jumlahArr) ||
                    empty($hargaArr) || !is_array($hargaArr)
                ) {
                    continue; // skip jika data tidak valid
                }

                $subtotalTotal = 0;
                $totalJumlahUtama = 0;

                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    if (!$satuan) continue;

                    $hargaSatuanRaw = $hargaArr[$satuanId] ?? 0;
                    $hargaSatuan = $sanitizeMoney($hargaSatuanRaw);
                    $subtotal = $qty * $hargaSatuan;
                    $subtotalTotal += $subtotal;

                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $totalJumlahUtama += $qty * $konversi;
                }

                TransaksiOfflineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'jumlah_json' => $jumlahArr,
                    'harga_json' => $hargaArr,
                    'subtotal' => $subtotalTotal,
                ]);

                $produk = \App\Models\Produk::findOrFail($produkId);

                if ($produk->stok < $totalJumlahUtama) {
                    \DB::rollBack();
                    return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                }

                $produk->stok -= $totalJumlahUtama;
                $produk->save();

                \App\Models\Stok::create([
                    'produk_id' => $produkId,
                    'jenis' => 'keluar',
                    'jumlah' => $totalJumlahUtama,
                    'keterangan' => 'Update stok transaksi ' . $transaksi->kode_transaksi,
                ]);
            }

            // Update keuangan
            $keuangan = \App\Models\Keuangan::where('transaksi_id', $transaksi->id)->first();
            if ($keuangan) {
                $keuangan->update([
                    'tanggal' => $request->tanggal,
                    'nominal' => $transaksi->total,
                    'keterangan' => 'Pemasukan dari transaksi #' . $transaksi->kode_transaksi,
                    'sumber' => 'offline',
                ]);
            }

            \DB::commit();

            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }



    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $transaksi = \App\Models\TransaksiOffline::with('detail')->findOrFail($id);

            foreach ($transaksi->detail as $detail) {
                $jumlahArr = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);
                if (!$jumlahArr) $jumlahArr = [];

                $totalJumlahUtama = 0;
                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    if (!$satuan) continue;

                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $totalJumlahUtama += $qty * $konversi;
                }

                $produk = $detail->produk;
                if ($produk) {
                    $produk->stok += $totalJumlahUtama;
                    $produk->save();

                    \App\Models\Stok::create([
                        'produk_id' => $detail->produk_id,
                        'jenis' => 'masuk',
                        'jumlah' => $totalJumlahUtama,
                        'keterangan' => 'Transaksi dihapus (' . $transaksi->kode_transaksi . ')',
                    ]);
                }
            }

            // Hapus detail transaksi
            $transaksi->detail()->delete();

            // Hapus catatan keuangan terkait (jika ada)
            \App\Models\Keuangan::where('transaksi_id', $transaksi->id)->delete();

            // Hapus transaksi
            $transaksi->delete();

            DB::commit();

            return redirect()->route('transaksi_offline.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
