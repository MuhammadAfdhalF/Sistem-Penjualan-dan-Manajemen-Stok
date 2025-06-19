<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetailRiwayatBelanjaController extends Controller
{
    public function index($tipe, $id)
    {
        $user = Auth::user();
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';

        if ($tipe === 'online') {
            $transaksi = \App\Models\TransaksiOnline::with('detail.produk.satuans')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $transaksi = \App\Models\TransaksiOffline::with('detail.produk.satuans')
                ->where('id', $id)
                ->where('pelanggan_id', $user->id)
                ->firstOrFail();
        }

        return view('mobile.detail_riwayat_belanja', [
            'transaksi' => $transaksi,
            'tipe' => $tipe,
            'jenisPelanggan' => $jenisPelanggan,
            'activeMenu' => 'riwayat',
        ]);
    }
}
