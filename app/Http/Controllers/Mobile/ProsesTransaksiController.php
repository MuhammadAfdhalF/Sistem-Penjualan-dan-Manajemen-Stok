<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Keranjang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Produk;
use App\Models\Satuan;
use App\Models\HargaProduk;
use App\Models\TransaksiOnline;
use App\Models\TransaksiOnlineDetail;
// use App\Models\Keuangan; // Removed: Keuangan is not used here
use App\Models\Stok;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Artisan; // Removed: Artisan is not used here
use Illuminate\Support\Str;
// use App\Models\User; // Removed: User is not directly used here
use Illuminate\Support\Facades\Log;
use App\Helpers\MidtransSnap; // Ensure this helper is imported

class ProsesTransaksiController extends Controller
{
    /**
     * Handles the quick purchase form confirmation.
     * Stores product data in session and displays it for confirmation.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function formBelanjaCepat(Request $request)
    {
        Log::info('Method formBelanjaCepat accessed.');
        $user = Auth::user();
        $produkData = $request->input('produk_data', []);

        if (empty($produkData)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 produk.');
        }

        // Store product data in session for use in formBelanjaCepatStore
        session(['form_cepat_data' => $produkData]);

        $produkCollection = collect($produkData)->map(function ($item) {
            $produk = Produk::with('hargaProduks', 'satuans')->find($item['produk_id']);
            if (!$produk) return null;

            return (object)[
                'produk' => $produk,
                'jumlah_json' => $item['jumlah_json'],
            ];
        })->filter();

        return view('mobile.proses_transaksi', [
            'jenis' => $user->jenis_pelanggan ?? 'Individu',
            'keranjangs' => $produkCollection,
            'activeMenu' => 'formcepat',
            'from_form_cepat' => true,
        ]);
    }

    /**
     * Stores the quick purchase transaction.
     * Creates a pending transaction in the database and calls Midtrans Snap if payment_gateway is selected.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function formBelanjaCepatStore(Request $request)
    {
        Log::info('Method formBelanjaCepatStore accessed.');
        $user = Auth::user();
        $produkData = session('form_cepat_data', []);

        if (empty($produkData)) {
            return redirect()->route('mobile.form_belanja_cepat.index')->with('error', 'Sesi belanja Anda telah berakhir. Silakan ulangi.');
        }

        $request->validate([
            'metode_pengambilan' => 'required|in:ambil di toko,diantar',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'alamat_pengambilan' => 'required_if:metode_pengambilan,diantar|nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $total = 0;
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';
        $itemDetailsForMidtrans = []; // For Midtrans Snap payload

        // Calculate total price and prepare itemDetails for Midtrans
        foreach ($produkData as $item) {
            $produk = Produk::with('satuans')->findOrFail($item['produk_id']);
            $jumlahArr = $item['jumlah_json'];

            foreach ($jumlahArr as $satuanId => $qty) {
                $qty = floatval($qty);
                if ($qty <= 0) continue;

                $satuan = Satuan::findOrFail($satuanId);
                $harga = HargaProduk::where('produk_id', $produk->id)
                    ->where('satuan_id', $satuanId)
                    ->where('jenis_pelanggan', $jenisPelanggan)
                    ->value('harga') ?? 0;

                $total += $harga * $qty;

                $itemDetailsForMidtrans[] = [
                    'id' => $produk->id . '-' . $satuan->id,
                    'price' => (int) $harga,
                    'quantity' => (int) $qty,
                    'name' => $produk->nama_produk . ' (' . $satuan->nama_satuan . ')',
                ];
            }
        }

        // Generate initial transaction code (will be final and Midtrans order_id)
        $kode = 'TX-ON-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));

        DB::beginTransaction(); // Always start DB transaction here
        try {
            // IMPORTANT: Create TransaksiOnline and its details NOW
            $transaksi = TransaksiOnline::create([
                'user_id' => $user->id,
                'kode_transaksi' => $kode, // This will be the order_id for Midtrans
                'tanggal' => now(),
                'metode_pembayaran' => $request->metode_pembayaran,
                'snap_token' => null, // Will be filled if payment_gateway
                'payment_type' => null, // Will be filled if payment_gateway
                'status_pembayaran' => ($request->metode_pembayaran === 'payment_gateway') ? 'pending' : 'pending', // Initially pending
                'status_transaksi' => 'diproses', // Initial transaction status
                'catatan' => $request->catatan,
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
                'total' => $total,
            ]);
            Log::info('Initial TransaksiOnline created with code: ' . $transaksi->kode_transaksi);

            // Save transaction details to TransaksiOnlineDetail
            foreach ($produkData as $item) {
                $produk = Produk::with('satuans')->findOrFail($item['produk_id']);
                $jumlahArr = $item['jumlah_json'];
                $subtotalProduk = 0;
                $hargaArr = [];

                foreach ($jumlahArr as $satuanId => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;

                    $satuan = Satuan::findOrFail($satuanId);
                    $harga = HargaProduk::where('produk_id', $produk->id)
                        ->where('satuan_id', $satuanId)
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->value('harga') ?? 0;

                    $hargaArr[$satuanId] = $harga;
                    $subtotalProduk += $harga * $qty;
                    $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                    $jumlahUtama = $qty * $konversi;

                    // Check stock here, but stock deduction only if non-payment_gateway
                    if ($produk->stok < $jumlahUtama) {
                        DB::rollBack();
                        Log::error("Insufficient stock for product {$produk->nama_produk}. Available stock: {$produk->stok}, Requested: {$jumlahUtama}.");
                        return redirect()->back()->withInput()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }

                    // DEDUCT STOCK ONLY FOR NON-PAYMENT GATEWAY HERE
                    // For payment_gateway, stock deduction will be done in the webhook after successful payment.
                    if ($request->metode_pembayaran !== 'payment_gateway') {
                        $produk->decrement('stok', $jumlahUtama);
                        Log::info("Stock for product '{$produk->nama_produk}' reduced by {$jumlahUtama} main units (non-gateway).");

                        Stok::create([
                            'produk_id' => $produk->id,
                            'satuan_id' => $satuanId,
                            'jenis' => 'keluar',
                            'jumlah' => $jumlahUtama,
                            'keterangan' => 'Transaksi online #' . $kode,
                        ]);
                        Log::info("Stock out record for product '{$produk->nama_produk}' created (non-gateway).");
                    }
                }

                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'jumlah_json' => $jumlahArr, // This will be stored as JSON in DB
                    'harga_json' => $hargaArr,   // This will be stored as JSON in DB
                    'subtotal' => $subtotalProduk,
                ]);
                Log::info("Transaction detail for product '{$produk->nama_produk}' saved.");
            }

            // Clear quick form session data after it's retrieved and processed
            session()->forget('form_cepat_data');
            Log::info('Quick form session data cleared.');

            DB::commit(); // Commit the database transaction
            Log::info('Database transaction committed.');

            if ($request->metode_pembayaran === 'payment_gateway') {
                Log::info('Processing payment with Midtrans (payment_gateway) from quick form.');

                $customer = [
                    'first_name' => $user->nama,
                    'email' => $user->email,
                    'phone' => $user->no_hp,
                ];

                // IMPORTANT: custom_fields only send MINIMAL data
                // No need to send 'produk_data_raw' or 'keranjang_ids_raw' anymore
                // because transaction details are already in our DB.
                $custom_fields = [
                    'user_id' => $user->id,
                    // You can add other very short data if needed
                ];

                // Call Midtrans Snap
                $snapToken = MidtransSnap::generateSnapToken($transaksi->kode_transaksi, $total, $customer, $itemDetailsForMidtrans, $custom_fields);
                Log::info('Midtrans Snap Token generated from quick form.');

                // Update snap_token in the created TransaksiOnline
                $transaksi->update(['snap_token' => $snapToken]);

                return response()->json([
                    'snap_token' => $snapToken,
                    'order_id' => $transaksi->kode_transaksi,
                ]);
            } else {
                Log::info('Database transaction committed for non-gateway payment.');
                return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order (catch block): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Handles the cart checkout process.
     * Retrieves selected cart items and displays them for confirmation.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function keranjang(Request $request)
    {
        $user = Auth::user();
        $keranjangIds = $request->input('keranjang_id', []);

        if (empty($keranjangIds)) {
            return redirect()->route('mobile.keranjang.index')->with('error', 'Anda harus memilih item di keranjang terlebih dahulu.');
        }

        session(['keranjang_ids' => $keranjangIds]);

        $keranjangs = Keranjang::with('produk.hargaProduks', 'produk.satuans')
            ->where('user_id', $user->id)
            ->whereIn('id', $keranjangIds)
            ->get();

        return view('mobile.proses_transaksi', [
            'jenis' => $user->jenis_pelanggan ?? 'Individu',
            'keranjangs' => $keranjangs,
            'activeMenu' => 'keranjang',
        ]);
    }

    /**
     * Stores the cart transaction.
     * Creates a pending transaction in the database and calls Midtrans Snap if payment_gateway is selected.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Log::info('Method store (from cart) accessed.');
        $user = Auth::user();
        $keranjangIds = session('keranjang_ids', []);

        if (empty($keranjangIds)) {
            return redirect()->route('mobile.keranjang.index')->with('error', 'Sesi keranjang Anda telah berakhir. Silakan ulangi.');
        }

        $request->validate([
            'metode_pengambilan' => 'required|in:ambil di toko,diantar',
            'metode_pembayaran' => 'required|in:payment_gateway,cod,bayar_di_toko',
            'alamat_pengambilan' => 'required_if:metode_pengambilan,diantar|nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $keranjangs = $user->keranjangs()->whereIn('id', $keranjangIds)->get();

        if ($keranjangs->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada produk yang dipilih.');
        }

        $total = 0;
        $kode = 'TX-ON-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        $jenisPelanggan = $user->jenis_pelanggan ?? 'Individu';
        $itemDetailsForMidtrans = [];

        // Calculate total price from cart items and prepare itemDetails for Midtrans
        foreach ($keranjangs as $item) {
            $produk = $item->produk;
            $jumlahArr = $item->jumlah_json;

            foreach ($jumlahArr as $satuanId => $qty) {
                $qty = floatval($qty);
                if ($qty <= 0) continue;

                $satuan = Satuan::findOrFail($satuanId);
                $harga = HargaProduk::where('produk_id', $produk->id)
                    ->where('satuan_id', $satuanId)
                    ->where('jenis_pelanggan', $jenisPelanggan)
                    ->value('harga') ?? 0;

                $total += $harga * $qty;

                $itemDetailsForMidtrans[] = [
                    'id' => $produk->id . '-' . $satuan->id,
                    'price' => (int) $harga,
                    'quantity' => (int) $qty,
                    'name' => $produk->nama_produk . ' (' . $satuan->nama_satuan . ')',
                ];
            }
        }

        DB::beginTransaction(); // Always start DB transaction here
        try {
            // IMPORTANT: Create TransaksiOnline and its details NOW
            $transaksi = TransaksiOnline::create([
                'user_id' => $user->id,
                'kode_transaksi' => $kode, // This will be the order_id for Midtrans
                'tanggal' => now(),
                'metode_pembayaran' => $request->metode_pembayaran,
                'snap_token' => null, // Will be filled if payment_gateway
                'payment_type' => null, // Will be filled if payment_gateway
                'status_pembayaran' => ($request->metode_pembayaran === 'payment_gateway') ? 'pending' : 'pending', // Initially pending
                'status_transaksi' => 'diproses',
                'catatan' => $request->catatan,
                'metode_pengambilan' => $request->metode_pengambilan,
                'alamat_pengambilan' => $request->metode_pengambilan === 'diantar' ? $request->alamat_pengambilan : null,
                'total' => $total,
            ]);
            Log::info('Initial TransaksiOnline created with code: ' . $transaksi->kode_transaksi);

            // Save transaction details to TransaksiOnlineDetail
            foreach ($keranjangs as $item) {
                $produk = $item->produk;
                $jumlahArr = $item->jumlah_json;
                $subtotalProduk = 0;
                $hargaArr = [];

                foreach ($jumlahArr as $satuanId => $qty) {
                    $qty = floatval($qty);
                    if ($qty <= 0) continue;

                    $satuan = Satuan::findOrFail($satuanId);
                    $harga = HargaProduk::where('produk_id', $produk->id)
                        ->where('satuan_id', $satuanId)
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->value('harga') ?? 0;

                    $hargaArr[$satuanId] = $harga;
                    $subtotalProduk += $harga * $qty;
                    $konversi = $satuan->konversi_ke_satuan_utama ?: 1;
                    $jumlahUtama = $qty * $konversi;

                    // Check stock here, but stock deduction only if non-payment_gateway
                    if ($produk->stok < $jumlahUtama) {
                        DB::rollBack();
                        Log::error("Insufficient stock for product {$produk->nama_produk}. Available stock: {$produk->stok}, Requested: {$jumlahUtama}.");
                        return redirect()->back()->withInput()->with('error', "Stok tidak cukup untuk produk {$produk->nama_produk}.");
                    }

                    // DEDUCT STOCK ONLY FOR NON-PAYMENT GATEWAY HERE
                    // For payment_gateway, stock deduction will be done in the webhook after successful payment.
                    if ($request->metode_pembayaran !== 'payment_gateway') {
                        $produk->decrement('stok', $jumlahUtama);
                        Log::info("Stock for product '{$produk->nama_produk}' reduced by {$jumlahUtama} main units (non-gateway).");

                        Stok::create([
                            'produk_id' => $produk->id,
                            'satuan_id' => $satuanId,
                            'jenis' => 'keluar',
                            'jumlah' => $jumlahUtama,
                            'keterangan' => 'Transaksi online #' . $kode,
                        ]);
                        Log::info("Stock out record for product '{$produk->nama_produk}' created (non-gateway).");
                    }
                }

                TransaksiOnlineDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $produk->id,
                    'jumlah_json' => $jumlahArr, // This will be stored as JSON in DB
                    'harga_json' => $hargaArr,   // This will be stored as JSON in DB
                    'subtotal' => $subtotalProduk,
                ]);
                Log::info("Transaction detail for product '{$produk->nama_produk}' saved.");
            }

            // Clear user's cart after data is retrieved and processed
            $user->keranjangs()->whereIn('id', $keranjangIds)->delete();
            session()->forget('keranjang_ids');
            Log::info('User cart cleared.');

            DB::commit(); // Commit the database transaction
            Log::info('Database transaction committed.');

            if ($request->metode_pembayaran === 'payment_gateway') {
                Log::info('Processing payment with Midtrans from cart (payment_gateway).');

                $customer = [
                    'first_name' => $user->nama,
                    'email' => $user->email,
                    'phone' => $user->no_hp,
                ];

                // IMPORTANT: custom_fields only send MINIMAL data
                // No need to send 'produk_data_raw' or 'keranjang_ids_raw' anymore
                // because transaction details are already in our DB.
                $custom_fields = [
                    'user_id' => $user->id,
                    // You can add other very short data if needed
                ];

                // Call Midtrans Snap
                $snapToken = MidtransSnap::generateSnapToken($transaksi->kode_transaksi, $total, $customer, $itemDetailsForMidtrans, $custom_fields);
                Log::info('Midtrans Snap Token generated from cart.');

                // Update snap_token in the created TransaksiOnline
                $transaksi->update(['snap_token' => $snapToken]);

                return response()->json([
                    'snap_token' => $snapToken,
                    'order_id' => $transaksi->kode_transaksi,
                ]);
            } else {
                Log::info('Database transaction committed for non-gateway payment.');
                return redirect()->route('mobile.home.index')->with('success', 'Pesanan berhasil dibuat!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order (catch block): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Private method to process transactions that do not use a payment gateway.
     * This method is no longer called to create transactions,
     * but only to update status and clean up if necessary
     * (however, this logic has been inlined into formBelanjaCepatStore and store).
     * This method can be removed or adapted if there are other needs.
     */
    // private function prosesTransaksiSelesai($itemsData, $user, $kode, Request $request, $jenisPelanggan, $flowType)
    // {
    //     // Logic in this method has been moved to formBelanjaCepatStore and store
    //     // You can remove this method if there are no other calls
    //     // or adapt it for other purposes if needed.
    //     Log::warning('Method prosesTransaksiSelesai called, but its logic has been inlined into store methods.');
    //     return redirect()->route('mobile.home.index')->with('warning', 'Transaction flow has been updated.');
    // }
}
