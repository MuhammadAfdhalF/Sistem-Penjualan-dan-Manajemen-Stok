<?php

namespace App\Http\Controllers;

use App\Models\PaymentLog;
use Illuminate\Http\Request;

class PaymentLogController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            $logs = PaymentLog::with('transaksi')->latest()->paginate(20);
        } else {
            // Jika pelanggan, batasi hanya log terkait transaksi miliknya
            $logs = PaymentLog::whereHas('transaksi', function ($query) {
                $query->where('user_id', auth()->id());
            })->with('transaksi')->latest()->paginate(20);
        }

        return view('payment_logs.index', compact('logs'));
    }
}
