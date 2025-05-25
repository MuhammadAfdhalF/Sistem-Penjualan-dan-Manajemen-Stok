<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Produk;
use Illuminate\Support\Facades\Log;
use App\Models\Keuangan;
use Carbon\Carbon;
use App\Models\TransaksiOfflineDetail;
use App\Models\TransaksiOnlineDetail;
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


        // Gabungkan dua query manual dengan Query Builder
        $gabung = DB::table('transaksi_offline_detail')
            ->select('produk_id', DB::raw('SUM(jumlah) as total'))
            ->groupBy('produk_id')
            ->unionAll(
                DB::table('transaksi_online_detail')
                    ->select('produk_id', DB::raw('SUM(jumlah) as total'))
                    ->groupBy('produk_id')
            );

        // Bungkus dalam subquery agar bisa diolah
        $produkTerlaris = DB::table(DB::raw("({$gabung->toSql()}) as gabungan"))
            ->mergeBindings($gabung) // penting: agar binding parameter tetap valid
            ->select('produk_id', DB::raw('SUM(total) as total_terjual'))
            ->groupBy('produk_id')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $produk = Produk::with('satuans')->find($item->produk_id);
                return [
                    'nama' => $produk?->nama_produk ?? 'Produk Tidak Ditemukan',
                    'total' => $produk?->tampilkanStok3Tingkatan($item->total_terjual) ?? $item->total_terjual
                ];
            });

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
            'pendapatanBersihHariIni'
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
