<?php
namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    class TransaksiOnlineDetailController extends Controller
    {
        public function index()
        {
            $detail = \App\Models\TransaksiOnlineDetail::with('produk', 'transaksi')->latest()->get();
            return view('transaksi_online_detail.index', compact('detail'));
        }
    }
