<?php

namespace App\Http\Controllers;

use App\Models\TransaksiOnline;
use App\Models\TransaksiOnlineDetail;
use App\Models\Produk;
use App\Models\HargaProduk;
use App\Models\Satuan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class TransaksiOnlineController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua user pelanggan untuk dropdown filter
        $users = User::where('role', 'pelanggan')->orderBy('nama')->get();

        // Mulai query untuk TransaksiOnline
        $query = TransaksiOnline::with('user')->latest();

        // Filter: TIDAK MENAMPILKAN TRANSAKSI DENGAN STATUS PEMBAYARAN 'gagal'
        $query->where('status_pembayaran', '!=', 'gagal');

        // Filter berdasarkan tanggal, bulan, tahun
        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->date);
        }
        if ($request->filled('month')) {
            $query->whereMonth('tanggal', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('tanggal', $request->year);
        }

        // Filter by user_id (pelanggan)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by metode_pembayaran (BARU DITAMBAHKAN)
        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        $transaksis = $query->get();

        // Ambil nilai filter yang dipilih untuk dikirim kembali ke view
        $filterMetodePembayaran = $request->metode_pembayaran;

        return view('transaksi_online.index', compact('transaksis', 'users', 'filterMetodePembayaran'));
    }

    public function create()
    {
        $users = User::where('role', 'pelanggan')->get();
        $produk = Produk::with('satuans')->get(); // penting!

        // Cek isi produk & satuans
        foreach ($produk as $prod) {
            \Log::info('Produk: ' . $prod->nama_produk . ' | Satuans: ' . json_encode($prod->satuans));
        }

        return view('transaksi_online.create', [
            'users' => $users,
            'produk' => $produk
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
            'metode_pengambilan' => 'required|in:ambil di toko,diantar', // Menambahkan validasi untuk metode_pengambilan
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
                'metode_pengambilan' => $request->metode_pengambilan, // Menyimpan metode pengambilan
                'alamat_pengambilan' => $request->alamat_pengambilan,
                'total' => 0,
            ]);

            foreach ($request->produk_id as $i => $produkId) {
                $jumlahJson = $request->jumlah_json[$i] ?? '{}';
                $jumlahArr = is_array($jumlahJson) ? $jumlahJson : json_decode($jumlahJson, true);
                if (!is_array($jumlahArr) || empty($jumlahArr)) continue;

                $subtotalProduk = 0;
                $hargaArr = []; // harga per satuan
                $produk = Produk::findOrFail($produkId);

                foreach ($jumlahArr as $satuanId => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;
                    $satuan = Satuan::findOrFail($satuanId);

                    // Ambil harga fix dari master harga_produk sesuai jenis pelanggan
                    $harga = HargaProduk::where('produk_id', $produkId)
                        ->where('satuan_id', $satuanId)
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->value('harga') ?? 0;

                    $hargaArr[$satuanId] = $harga; // simpan harga per satuan
                    $subtotalProduk += $harga * $qty;

                    // Konversi jumlah ke satuan utama untuk update stok
                    $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                    $jumlahUtama = $qty * $konversi;
                    if ($produk->stok < $jumlahUtama) {
                        DB::rollBack();
                        return redirect()->back()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }
                    $produk->stok -= $jumlahUtama;
                    $produk->save();

                    // Catat log stok keluar
                    \App\Models\Stok::create([
                        'produk_id' => $produkId,
                        'satuan_id' => $satuanId,
                        'jenis' => 'keluar',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Transaksi online #' . $kode,
                    ]);
                }

                // Simpan detail transaksi (sudah mirip offline)
                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produkId,
                    'jumlah_json' => $jumlahArr,
                    'harga_json' => $hargaArr,
                    'subtotal' => $subtotalProduk,
                ]);

                $total += $subtotalProduk;
            }

            $transaksi->update(['total' => $total]);

            // Simpan keuangan hanya jika status pembayaran lunas
            if ($request->status_pembayaran === 'lunas') {
                \App\Models\Keuangan::create([
                    'transaksi_online_id' => $transaksi->id,
                    'tanggal' => $request->tanggal,
                    'jenis' => 'pemasukan',
                    'nominal' => $total,
                    'keterangan' => 'Pemasukan dari transaksi online #' . $kode,
                    'sumber' => 'online',
                ]);
            }

            DB::commit();

            return redirect()->route('transaksi_online.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $transaksiOnline = \App\Models\TransaksiOnline::with([
            'user',
            'detail.produk',
        ])->findOrFail($id);

        return view('transaksi_online.show', compact('transaksiOnline'));
    }


    public function edit($id)
    {
        $users = \App\Models\User::where('role', 'pelanggan')->get();
        $produk = \App\Models\Produk::with('satuans')->get();
        $transaksiOnline = \App\Models\TransaksiOnline::with(['detail.produk', 'detail.produk.satuans', 'user'])->findOrFail($id);

        return view('transaksi_online.edit', compact('transaksiOnline', 'users', 'produk'));
    }

    public function update(Request $request, $id)
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

        \DB::beginTransaction();
        try {
            $transaksiOnline = \App\Models\TransaksiOnline::with('detail.produk')->findOrFail($id);

            // Rollback stok lama dari detail lama
            foreach ($transaksiOnline->detail as $detail) {
                $jumlahArr = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);
                if (!$jumlahArr) $jumlahArr = [];
                $produk = $detail->produk;
                if (!$produk) continue;
                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    $jumlahUtama = floatval($qty) * $konversi;
                    $produk->stok += $jumlahUtama;
                    $produk->save();

                    \App\Models\Stok::create([
                        'produk_id' => $detail->produk_id,
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
                'metode_pengambilan' => $request->metode_pengambilan, // Update metode pengambilan
                'alamat_pengambilan' => $request->alamat_pengambilan,
            ]);

            // Hapus detail lama
            $transaksiOnline->detail()->delete();

            // Hapus data keuangan lama yang terkait transaksi ini
            \App\Models\Keuangan::where('transaksi_online_id', $transaksiOnline->id)->delete();

            // Jenis pelanggan baru (untuk harga)
            $user = \App\Models\User::findOrFail($request->user_id);
            $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

            $total = 0;

            foreach ($request->produk_id as $i => $produkId) {
                $jumlahArr = json_decode($request->jumlah_json[$i], true);
                if (!is_array($jumlahArr) || empty($jumlahArr)) continue;

                $subtotalProduk = 0;
                $produk = \App\Models\Produk::findOrFail($produkId);

                foreach ($jumlahArr as $satuanId => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;
                    $satuan = \App\Models\Satuan::findOrFail($satuanId);

                    $harga = \App\Models\HargaProduk::where('produk_id', $produkId)
                        ->where('satuan_id', $satuanId)
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->value('harga') ?? 0;

                    $subtotalProduk += $harga * $qty;

                    $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                    $jumlahUtama = $qty * $konversi;
                    if ($produk->stok < $jumlahUtama) {
                        \DB::rollBack();
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

                \App\Models\TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksiOnline->id,
                    'produk_id' => $produkId,
                    'jumlah_json' => $jumlahArr,
                    'subtotal' => $subtotalProduk, // <-- Fix: Tambahkan field subtotal!
                ]);

                $total += $subtotalProduk;
            }

            $transaksiOnline->update(['total' => $total]);

            // Simpan keuangan baru jika pembayaran lunas
            if ($request->status_pembayaran === 'lunas') {
                \App\Models\Keuangan::create([
                    'transaksi_online_id' => $transaksiOnline->id,
                    'tanggal' => $request->tanggal,
                    'jenis' => 'pemasukan',
                    'nominal' => $total,
                    'keterangan' => 'Pemasukan dari transaksi online #' . $transaksiOnline->kode_transaksi,
                    'sumber' => 'online',
                ]);
            }

            \DB::commit();

            return redirect()->route('transaksi_online.index')->with('success', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }

    public function destroy(TransaksiOnline $transaksiOnline)
    {
        \DB::beginTransaction();
        try {
            // Pastikan eager load detail + produk biar efisien
            $transaksiOnline->load('detail.produk');

            // Rollback stok produk untuk setiap detail
            foreach ($transaksiOnline->detail as $detail) {
                $jumlahArr = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);
                if (!$jumlahArr) $jumlahArr = [];
                $produk = $detail->produk;
                if (!$produk) continue;

                foreach ($jumlahArr as $satuanId => $qty) {
                    $satuan = \App\Models\Satuan::find($satuanId);
                    if (!$satuan) continue;

                    $konversi = $satuan->konversi_ke_satuan_utama ?? 1;
                    $jumlahUtama = floatval($qty) * $konversi;
                    $produk->stok += $jumlahUtama;
                    $produk->save();

                    \App\Models\Stok::create([
                        'produk_id' => $detail->produk_id,
                        'satuan_id' => $satuan->id,
                        'jenis' => 'masuk',
                        'jumlah' => $jumlahUtama,
                        'keterangan' => 'Transaksi online dihapus (#' . $transaksiOnline->kode_transaksi . ')',
                    ]);
                }
            }

            // Hapus semua detail transaksi online
            $transaksiOnline->detail()->delete();

            // Hapus catatan keuangan terkait (transaksi_online_id)
            \App\Models\Keuangan::where('transaksi_online_id', $transaksiOnline->id)->delete();

            // Hapus transaksi online utama
            $transaksiOnline->delete();

            \DB::commit();

            return redirect()->route('transaksi_online.index')->with('success', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
