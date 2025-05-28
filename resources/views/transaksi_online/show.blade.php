@extends('layouts.mantis')

@section('title')
Detail Transaksi Online
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Detail Transaksi</h4>
        <a href="{{ route('transaksi_online.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <h5><strong>Kode Transaksi:</strong> {{ $transaksiOnline->kode_transaksi }}</h5>
        <p><strong>Pelanggan:</strong> {{ $transaksiOnline->user->nama ?? '-' }}</p>
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaksiOnline->tanggal)->format('d-m-Y H:i') }}</p>
        <p><strong>Metode Pembayaran:</strong> {{ ucfirst(str_replace('_', ' ', $transaksiOnline->metode_pembayaran)) }}</p>
        <p><strong>Status Pembayaran:</strong> {{ ucfirst($transaksiOnline->status_pembayaran) }}</p>
        <p><strong>Status Transaksi:</strong> {{ ucfirst($transaksiOnline->status_transaksi) }}</p>
        <p><strong>Diambil di Toko:</strong> {{ $transaksiOnline->diambil_di_toko ? 'Ya' : 'Tidak' }}</p>
        <p><strong>Alamat Pengambilan / Pengiriman:</strong> {{ $transaksiOnline->alamat_pengambilan ?? '-' }}</p>
        <p><strong>Catatan:</strong> {{ $transaksiOnline->catatan ?? '-' }}</p>

        <h5 class="mt-4">Detail Produk</h5>
        <div class="table-responsive">
            <table class="table table-bordered mt-2">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    use App\Models\Satuan;
                    use App\Models\HargaProduk;
                    $totalTransaksi = 0;
                    @endphp
                    @foreach ($transaksiOnline->detail as $item)
                    @php
                    $jumlahLabel = [];
                    $subtotalProduk = 0;
                    foreach (($item->jumlah_json ?? []) as $satuanId => $jumlah) {
                    $satuan = Satuan::find($satuanId);
                    $namaSatuan = $satuan->nama_satuan ?? '-';
                    if ($jumlah > 0) {
                    $jumlahLabel[] = $jumlah . ' ' . $namaSatuan;
                    }
                    // Hitung subtotal per satuan
                    $harga = HargaProduk::where('produk_id', $item->produk_id)
                    ->where('satuan_id', $satuanId)
                    ->where('jenis_pelanggan', $transaksiOnline->user->jenis_pelanggan ?? 'Individu')
                    ->value('harga') ?? 0;
                    $subtotalProduk += $harga * $jumlah;
                    }
                    $totalTransaksi += $subtotalProduk;
                    @endphp
                    <tr>
                        <td>{{ $item->produk->nama_produk ?? '-' }}</td>
                        <td>{{ implode(' ', $jumlahLabel) }}</td>
                        <td>Rp {{ number_format($subtotalProduk, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">Total</th>
                        <th>Rp {{ number_format($totalTransaksi, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection