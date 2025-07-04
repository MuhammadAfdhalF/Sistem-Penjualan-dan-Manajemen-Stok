<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TransaksiOnline;
use App\Models\TransaksiOffline;


class RiwayatBelanjaController extends Controller
{
    /**
     * Menampilkan riwayat belanja pengguna (online dan offline),
     * tidak termasuk transaksi dengan status pembayaran 'gagal'.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

        // Ambil transaksi online user, KECUALI yang status_pembayaran-nya 'gagal'
        $online = TransaksiOnline::with('detail.produk')
            ->where('user_id', $user->id)
            ->where('status_pembayaran', '!=', 'gagal') // Filter transaksi online yang gagal
            ->get()
            ->map(function ($item) {
                $item->tipe = 'online';
                return $item;
            });

        // Ambil transaksi offline user, KECUALI yang status_pembayaran-nya 'gagal'
        $offline = TransaksiOffline::with('detail.produk')
            ->where('pelanggan_id', $user->id)
            ->where('status_pembayaran', '!=', 'gagal') // Filter transaksi offline yang gagal
            ->get()
            ->map(function ($item) {
                $item->tipe = 'offline';
                return $item;
            });

        // Gabungkan dan urutkan semua transaksi
        $riwayat = $online->concat($offline)->sortByDesc('tanggal');

        return view('mobile.riwayat_belanja', [
            'activeMenu' => 'riwayat',
            'jenisPelanggan' => $jenisPelanggan,
            'riwayat' => $riwayat,
        ]);
    }
}
