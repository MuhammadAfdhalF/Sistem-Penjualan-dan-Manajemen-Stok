@extends('layouts.mantis')

@section('title')
Detail Transaksi
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Detail Transaksi Offline</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive"> <!-- Tambahkan ini -->
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi_detail as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->transaksi->kode_transaksi }}</td>
                            <td>{{ $item->produk->nama_produk }}</td>
                            <td>{{ $item->jumlah }}</td>
                            <td>
                                Rp
                                @if($item->tipe_harga === 'grosir')
                                {{ number_format($item->produk->harga_grosir, 0, ',', '.') }}
                                @else
                                {{ number_format($item->produk->harga_normal, 0, ',', '.') }}
                                @endif
                            </td>
                            <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- Tutup table-responsive -->
        </div>
    </div>
</div>
@endsection