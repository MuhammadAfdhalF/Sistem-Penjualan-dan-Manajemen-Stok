<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Produk;
use Illuminate\Support\Facades\Log;
use App\Models\Keuangan;
use Carbon\Carbon;
use App\Models\TransaksiOfflineDetail;
use App\Models\TransaksiOnlineDetail;
use App\Models\Satuan;

use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index()
    {
        // Ambil semua produk
        $produk = Produk::all();
        Log::info('Memulai proses view produk ID: ' . $produk);

        // Pisahkan produk berdasarkan status stok vs ROP
        $produkMenipis = $produk->filter(function ($item) {
            return $item->stok <= $item->rop;
        });

        // Hitung pelanggan berdasarkan jenis
        $totalIndividu = User::where('role', 'pelanggan')
            ->where('jenis_pelanggan', 'Individu')
            ->count();

        $totalToko = User::where('role', 'pelanggan')
            ->where('jenis_pelanggan', 'Toko Kecil')
            ->count();

        // Data keuangan hari ini (box)
        $hariIni = Carbon::today();

        $pemasukanHariIni = Keuangan::where('jenis', 'pemasukan')
            ->whereDate('tanggal', $hariIni)
            ->sum('nominal');

        $pengeluaranHariIni = Keuangan::where('jenis', 'pengeluaran')
            ->whereDate('tanggal', $hariIni)
            ->sum('nominal');

        $pendapatanBersihHariIni = $pemasukanHariIni - $pengeluaranHariIni;

        //total produk
        $totalProduk = Produk::count();

        //keuangan
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        $totalPemasukanBulanIni = Keuangan::where('jenis', 'pemasukan')
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->sum('nominal');

        $totalPengeluaranBulanIni = Keuangan::where('jenis', 'pengeluaran')
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->sum('nominal');

        $totalPendapatanBersih = $totalPemasukanBulanIni - $totalPengeluaranBulanIni;

        //stok kurang
        $produkMenipis = Produk::with('satuans')
            ->get()
            ->filter(fn($item) => $item->stok <= $item->rop);

        // Tambahkan variabel tahun
        $tahunSekarang = date('Y');
        $tahunMulai = 2022;


        // Ambil data produk terjual gabungan offline + online
        $produkTerjual = [];

        // Hitung produk terjual dari transaksi offline (jumlah biasa dengan satuan)
        $offlineDetails = TransaksiOfflineDetail::all();
        foreach ($offlineDetails as $detail) {
            $produkId = $detail->produk_id;
            $satuan = Satuan::find($detail->satuan_id);
            $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
            $qtyUtama = floatval($detail->jumlah) * $konversi;

            if (!isset($produkTerjual[$produkId])) {
                $produkTerjual[$produkId] = 0;
            }
            $produkTerjual[$produkId] += $qtyUtama;
        }

        // Hitung produk terjual dari transaksi online (jumlah bertingkat JSON)
        $onlineDetails = TransaksiOnlineDetail::all();
        foreach ($onlineDetails as $detail) {
            $produkId = $detail->produk_id;
            $jumlahJson = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);

            $totalQtyUtama = 0;
            foreach ($jumlahJson as $satuanId => $qty) {
                $satuan = Satuan::find($satuanId);
                $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                $totalQtyUtama += $qty * $konversi;
            }

            if (!isset($produkTerjual[$produkId])) {
                $produkTerjual[$produkId] = 0;
            }
            $produkTerjual[$produkId] += $totalQtyUtama;
        }

        // Urutkan produk terlaris berdasarkan total qty satuan utama
        arsort($produkTerjual);

        // Ambil 5 produk terlaris dan format tampilannya
        $produkTerlaris = collect($produkTerjual)
            ->take(5)
            ->map(function ($total, $produkId) {
                $produk = \App\Models\Produk::with('satuans')->find($produkId);
                return [
                    'nama' => $produk?->nama_produk ?? 'Produk Tidak Ditemukan',
                    // Fungsi tampilkanStok3Tingkatan harus mengubah jumlah utama ke string bertingkat, misal "1 slof 4 bks"
                    'total' => $produk?->tampilkanStok3Tingkatan($total) ?? (int) $total,
                ];
            })
            ->values();


        // ====== PELANGGAN TERROYAL (Gabungan online + offline) ======
        $gabungUser = DB::table('transaksi_offline')
            ->select('pelanggan_id as user_id', DB::raw('SUM(total) as total'))
            ->whereNotNull('pelanggan_id')
            ->groupBy('pelanggan_id')
            ->unionAll(
                DB::table('transaksi_online')
                    ->select('user_id', DB::raw('SUM(total) as total'))
                    ->whereNotNull('user_id')
                    ->groupBy('user_id')
            );

        $pelangganTerroyalGabung = DB::table(DB::raw("({$gabungUser->toSql()}) as gabungan"))
            ->mergeBindings($gabungUser)
            ->select('user_id', DB::raw('SUM(total) as total_belanja'))
            ->groupBy('user_id');

        $pelanggan = DB::table('users')
            ->joinSub($pelangganTerroyalGabung, 'total_belanja', function ($join) {
                $join->on('users.id', '=', 'total_belanja.user_id');
            })
            ->where('users.role', 'pelanggan')
            ->select('users.id', 'users.nama', 'users.jenis_pelanggan', 'total_belanja.total_belanja')
            ->get();

        // Bagi dua: individu dan toko kecil
        $terroyalIndividu = $pelanggan->where('jenis_pelanggan', 'Individu')
            ->sortByDesc('total_belanja')
            ->take(5)
            ->values();

        $terroyalToko = $pelanggan->where('jenis_pelanggan', 'Toko Kecil')
            ->sortByDesc('total_belanja')
            ->take(5)
            ->values();




        return view('dashboard.index', compact(
            'produk',
            'produkMenipis',
            'totalIndividu',
            'totalToko',
            'totalProduk',
            'totalPemasukanBulanIni',
            'totalPengeluaranBulanIni',
            'totalPendapatanBersih',
            'tahunSekarang',
            'tahunMulai',
            'produkTerlaris',
            'pemasukanHariIni',
            'pengeluaranHariIni',
            'pendapatanBersihHariIni',
            'terroyalIndividu',
            'terroyalToko'

        ));
    }

    public function getRingkasanKeuanganBulanan($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $data = [
            'pemasukan' => array_fill(1, 12, 0),
            'pengeluaran' => array_fill(1, 12, 0),
            'pendapatan_bersih' => array_fill(1, 12, 0),
        ];

        $keuangan = Keuangan::selectRaw('MONTH(tanggal) as bulan')
            ->selectRaw('SUM(CASE WHEN jenis = "pemasukan" THEN nominal ELSE 0 END) as total_pemasukan')
            ->selectRaw('SUM(CASE WHEN jenis = "pengeluaran" THEN nominal ELSE 0 END) as total_pengeluaran')
            ->whereYear('tanggal', $tahun)
            ->groupByRaw('MONTH(tanggal)')
            ->get();

        foreach ($keuangan as $item) {
            $bulan = (int) $item->bulan;
            $data['pemasukan'][$bulan] = (float) $item->total_pemasukan;
            $data['pengeluaran'][$bulan] = (float) $item->total_pengeluaran;
            $data['pendapatan_bersih'][$bulan] = $data['pemasukan'][$bulan] - $data['pengeluaran'][$bulan];
        }

        return response()->json($data);
    }

    public function getChartData($tipe, $tahun)
    {
        $tahun = $tahun ?? date('Y');
        $data = array_fill(1, 12, 0);

        if ($tipe === 'bersih') {
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $pemasukan = Keuangan::whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->where('jenis', 'pemasukan')
                    ->sum('nominal');

                $pengeluaran = Keuangan::whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->where('jenis', 'pengeluaran')
                    ->sum('nominal');

                $data[$bulan] = $pemasukan - $pengeluaran;
            }
        } else {
            $result = Keuangan::selectRaw('MONTH(tanggal) as bulan, SUM(nominal) as total')
                ->where('jenis', $tipe)
                ->whereYear('tanggal', $tahun)
                ->groupByRaw('MONTH(tanggal)')
                ->pluck('total', 'bulan');

            foreach ($result as $bulan => $total) {
                $data[(int)$bulan] = (float)$total;
            }
        }

        return response()->json(array_values($data));
    }
}
