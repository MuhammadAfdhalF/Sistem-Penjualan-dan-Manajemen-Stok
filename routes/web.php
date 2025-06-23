<?php

use App\Http\Controllers\BannerController;
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
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\TransaksiOnlineController;
use App\Http\Controllers\TransaksiOnlineDetailController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PaymentLogController;
use App\Http\Controllers\Mobile\CobaController;

use App\Http\Controllers\Mobile\DetailProdukController;
use App\Http\Controllers\Mobile\DetailRiwayatBelanjaController;
use App\Http\Controllers\Mobile\FormBelanjaCepatController;
use App\Http\Controllers\Mobile\HomeController as MobileHomeController;
use App\Http\Controllers\Mobile\KeranjangMobileController;
use App\Http\Controllers\Mobile\ProfilePelangganController;
use App\Http\Controllers\Mobile\ProsesTransaksiController;
use App\Http\Controllers\Mobile\RiwayatBelanjaController;
use App\Models\HargaProduk;

Route::get('/', function () {
    return redirect('/login');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');




// Group routes yang harus admin only
Route::middleware(['auth', 'adminonly'])->group(function () {
    //dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/keuangan-bulanan/{tahun?}', [DashboardController::class, 'getRingkasanKeuanganBulanan']);
    Route::get('/api/grafik/keuangan/{tipe}/{tahun}', [DashboardController::class, 'getChartData']);


    // produk
    Route::resource('produk', ProdukController::class);

    // satuan
    Route::resource('satuan', SatuanController::class);
    Route::get('/get-satuan-by-produk/{id}', [SatuanController::class, 'getSatuanByProduk']);


    // harga
    Route::resource('harga_produk', HargaProdukController::class);
    Route::get('/get-harga-produk', [\App\Http\Controllers\HargaProdukController::class, 'getHarga']);
    Route::get('/get-harga-produk', [HargaProdukController::class, 'getHargaByProduk']);
    Route::get('/get-harga-produk-all', [\App\Http\Controllers\HargaProdukController::class, 'getHargaAllByProduk']);



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

    // Banner
    Route::resource('banner', BannerController::class);



    // route tambahan kalau ada yang spesifik
    Route::get('/pegawai', function () {
        return view('pegawai');
    });
});


// Routes yang harus login, untuk pelanggan dan admin
Route::middleware(['auth'])->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::get('/keranjang/create', [KeranjangController::class, 'create'])->name('keranjang.create');
    Route::post('/keranjang', [KeranjangController::class, 'store'])->name('keranjang.store');
    Route::put('/keranjang/{id}', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/{id}', [KeranjangController::class, 'destroy'])->name('keranjang.destroy');

    Route::resource('payment_logs', PaymentLogController::class)->only(['index', 'show']);
});



Route::middleware(['auth', 'pelangganonly'])->group(function () {
    Route::get('/pelanggan-area/home', [MobileHomeController::class, 'index'])->name('mobile.home.index');
    Route::get('/pelanggan-area/detail_produk/{id}', [DetailProdukController::class, 'index'])->name('mobile.detail_produk.index');

    // keranjang
    Route::get('/pelanggan-area/keranjang', [KeranjangMobileController::class, 'keranjang'])->name('mobile.keranjang.index');
    Route::get('/pelanggan-area/keranjang/create', [KeranjangMobileController::class, 'create'])->name('mobile.keranjang.create');
    Route::post('/pelanggan-area/keranjang', [KeranjangMobileController::class, 'store'])->name('mobile.keranjang.store');
    Route::put('/pelanggan-area/keranjang/{id}', [KeranjangMobileController::class, 'update'])->name('mobile.keranjang.update');
    Route::delete('/pelanggan-area/keranjang/{id}', [KeranjangMobileController::class, 'destroy'])->name('mobile.keranjang.destroy');

    // proses transaksi
    Route::get('/pelanggan-area/proses_transaksi', [ProsesTransaksiController::class, 'keranjang'])->name('mobile.proses_transaksi.index');
    Route::post('/pelanggan-area/proses_transaksi', [ProsesTransaksiController::class, 'store'])->name('mobile.proses_transaksi.store');


    // form cepat
    Route::get('/pelanggan-area/form_belanja_cepat', [FormBelanjaCepatController::class, 'index'])->name('mobile.form_belanja_cepat.index');
    // Route::post('/pelanggan-area/form_belanja_cepat', [FormBelanjaCepatController::class, 'store'])->name('mobile.form_belanja_cepat.store');
    Route::post('/pelanggan-area/form_belanja_cepat/konfirmasi', [ProsesTransaksiController::class, 'formBelanjaCepat'])->name('mobile.form_belanja_cepat.konfirmasi');
    Route::post('/pelanggan-area/form_belanja_cepat/simpan', [ProsesTransaksiController::class, 'formBelanjaCepatStore'])->name('mobile.form_belanja_cepat.store');
    Route::post('/pelanggan-area/form_belanja_cepat/validate-checkout', [FormBelanjaCepatController::class, 'validateCheckout'])->name('mobile.form_belanja_cepat.validateCheckout'); // <--- TAMBAHKAN INI


    // Riwayat Belanja
    Route::get('/pelanggan-area/riwayat_belanja', [RiwayatBelanjaController::class, 'index'])->name('mobile.riwayat_belanja.index');

    // Detail Riwayat Belanja
    Route::get('/pelanggan-area/detail_riwayat_belanja/{tipe}/{id}', [DetailRiwayatBelanjaController::class, 'index'])
        ->name('mobile.detail_riwayat_belanja.index');

    // Riwayat Belanja
    Route::get('/pelanggan-area/profile_pelanggan', [ProfilePelangganController::class, 'index'])->name('mobile.profile_pelanggan.index');
    Route::get('/pelanggan-area/profile_pelanggan/edit', [ProfilePelangganController::class, 'edit'])->name('mobile.profile_pelanggan.edit');
    Route::put('/pelanggan-area/profile_pelanggan/update', [ProfilePelangganController::class, 'update'])->name('mobile.profile_pelanggan.update');
});






// fallback
Route::fallback(function () {
    return "Halaman tidak ada, akses halaman yang benar ya!!!";
});
