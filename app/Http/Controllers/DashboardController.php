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
        // Ambil semua produk dengan eager load satuan
        $produk = Produk::with('satuans')->get();

        // Produk dengan stok menipis (stok <= ROP)
        $produkMenipis = $produk->filter(fn($item) => $item->stok <= $item->rop);

        // Hitung pelanggan berdasarkan jenis
        $totalIndividu = User::where('role', 'pelanggan')->where('jenis_pelanggan', 'Individu')->count();
        $totalToko = User::where('role', 'pelanggan')->where('jenis_pelanggan', 'Toko Kecil')->count();

        // Data keuangan hari ini
        $hariIni = Carbon::today();

        $pemasukanHariIni = Keuangan::where('jenis', 'pemasukan')->whereDate('tanggal', $hariIni)->sum('nominal');
        $pengeluaranHariIni = Keuangan::where('jenis', 'pengeluaran')->whereDate('tanggal', $hariIni)->sum('nominal');
        $pendapatanBersihHariIni = $pemasukanHariIni - $pengeluaranHariIni;

        $totalProduk = $produk->count();

        // Keuangan bulan ini
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

        // Produk menipis (sudah eager load satuan)
        // $produkMenipis sudah didefinisikan di atas

        // Tahun sekarang dan mulai
        $tahunSekarang = date('Y');
        $tahunMulai = 2022;

        // Hitung produk terjual gabungan offline + online

        // Tahun saat ini untuk filter produk terlaris
        $tahunSaatIni = Carbon::now()->year; // <-- Tambahkan baris ini

        // Hitung produk terjual gabungan offline + online

        // Eager load produk untuk efisiensi
        $offlineDetails = TransaksiOfflineDetail::with('produk')
            ->whereYear('created_at', $tahunSaatIni) // <-- Tambahkan filter tahun di sini
            ->get();

        $produkTerjual = [];

        foreach ($offlineDetails as $detail) {
            $produkId = $detail->produk_id;
            $jumlahJson = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);

            $totalQtyUtama = 0;
            if (is_array($jumlahJson)) {
                foreach ($jumlahJson as $satuanId => $qty) {
                    $satuan = Satuan::find($satuanId);
                    $konversi = $satuan ? $satuan->konversi_ke_satuan_utama : 1;
                    $totalQtyUtama += $qty * $konversi;
                }
            }

            if (!isset($produkTerjual[$produkId])) {
                $produkTerjual[$produkId] = 0;
            }
            $produkTerjual[$produkId] += $totalQtyUtama;
        }


        $onlineDetails = TransaksiOnlineDetail::with('produk')
            ->whereYear('created_at', $tahunSaatIni) // <-- Tambahkan filter tahun di sini
            ->get();

        foreach ($onlineDetails as $detail) {
            $produkId = $detail->produk_id;
            // Handle jika jumlah_json null/kosong, tetap array kosong
            $jumlahJson = is_array($detail->jumlah_json) ? $detail->jumlah_json : json_decode($detail->jumlah_json, true);
            if (!is_array($jumlahJson)) $jumlahJson = [];

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


        arsort($produkTerjual);

        $produkTerlaris = collect($produkTerjual)
            ->take(5)
            ->map(function ($total, $produkId) {
                $produk = Produk::with('satuans')->find($produkId);
                return [
                    'nama' => $produk?->nama_produk ?? 'Produk Tidak Ditemukan',
                    'total' => $produk?->tampilkanStok3Tingkatan($total) ?? (int) $total,
                ];
            })
            ->values();

        // Pelanggan terroyal gabungan offline + online
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
        $terroyalIndividu = $pelanggan->where('jenis_pelanggan', 'Individu')->sortByDesc('total_belanja')->take(5)->values();
        $terroyalToko = $pelanggan->where('jenis_pelanggan', 'Toko Kecil')->sortByDesc('total_belanja')->take(5)->values();

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
