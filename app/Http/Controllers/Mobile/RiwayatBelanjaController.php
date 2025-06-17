<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TransaksiOnline;
use App\Models\TransaksiOffline;


class RiwayatBelanjaController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

        // Ambil transaksi online user
        $online = TransaksiOnline::with('detail.produk')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($item) {
                $item->tipe = 'online';
                return $item;
            });

        // Ambil transaksi offline user
        $offline = TransaksiOffline::with('detail.produk')
            ->where('pelanggan_id', $user->id)
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
