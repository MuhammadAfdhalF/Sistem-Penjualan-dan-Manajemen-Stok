<?php

use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\TransaksiOfflineController;
use App\Http\Controllers\TransaksiOfflineDetailController;
use App\Models\Pegawai;
use App\Models\TransaksiOffline;
use App\Models\TransaksiOfflineDetail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HargaProdukController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\TransaksiOnlineController;
use App\Http\Controllers\TransaksiOnlineDetailController;
use App\Models\HargaProduk;

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Group routes yang harus admin only
Route::middleware(['auth', 'adminonly'])->group(function () {
    //dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // produk
    Route::resource('produk', ProdukController::class);

    // satuan
    Route::resource('satuan', SatuanController::class);
    Route::get('/get-satuan-by-produk/{id}', [SatuanController::class, 'getSatuanByProduk']);


    // harga
    Route::resource('harga_produk', HargaProdukController::class);
    Route::get('/get-harga-produk', [\App\Http\Controllers\HargaProdukController::class, 'getHarga']);
    Route::get('/get-harga-produk', [HargaProdukController::class, 'getHargaByProduk']);


    // stok
    Route::resource('stok', StokController::class);

    // transaksi offline
    Route::resource('transaksi_offline', TransaksiOfflineController::class);

    // transaksi offline detail
    Route::resource('transaksi_offline_detail', TransaksiOfflineDetailController::class);

        // transaksi online
    Route::resource('transaksi_online', TransaksiOnlineController::class);

    // transaksi online detail
    Route::resource('transaksi_online_detail', TransaksiOnlineDetailController::class);

    // Pelanggan
    Route::resource('pelanggan', PelangganController::class);

    // keuangan
    Route::resource('keuangan', KeuanganController::class);

    // pegawai
    Route::resource('pegawai', PegawaiController::class);

    // route tambahan kalau ada yang spesifik
    Route::get('/pegawai', function () {
        return view('pegawai');
    });
});

// fallback
Route::fallback(function () {
    return "Halaman tidak ada, akses halaman yang benar ya dek !!!";
});
