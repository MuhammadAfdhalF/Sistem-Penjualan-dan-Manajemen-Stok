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
        <p><strong>Total:</strong> Rp {{ number_format($transaksiOnline->total, 0, ',', '.') }}</p>
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
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksiOnline->detail as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->produk->nama_produk ?? '-' }}</td>
                        <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td>{{ $item->jumlah }}</td>
                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection