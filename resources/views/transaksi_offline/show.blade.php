@extends('layouts.mantis')

@section('title')
Detail Transaksi Offline
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Detail Transaksi</h4>
        <a href="{{ route('transaksi_offline.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <h5><strong>Kode Transaksi:</strong> {{ $transaksi->kode_transaksi }}</h5>
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($transaksi->total, 0, ',', '.') }}</p>
        <p><strong>Dibayar:</strong> Rp {{ number_format($transaksi->dibayar, 0, ',', '.') }}</p>
        <p><strong>Kembalian:</strong> Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</p>

        <h5 class="mt-4">Detail Produk</h5>
        <div class="table-responsive">
            <table class="table table-bordered mt-2">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksi->detail as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->produk->nama_produk ?? $item->produk->nama }}</td>
                        <td>
                            @php
                            // Tentukan harga sesuai tipe_harga yang tersimpan
                            $harga = $item->tipe_harga === 'grosir'
                            ? $item->produk->harga_grosir
                            : $item->produk->harga_normal;
                            // Jika harga pada detail sudah ada override, bisa ganti $item->harga
                            // Jika mau tetap yang ada di transaksi detail (misal sudah disimpan), gunakan:
                            // $harga = $item->harga;
                            @endphp
                            Rp {{ number_format($harga, 0, ',', '.') }}
                        </td>
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