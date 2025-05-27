@extends('layouts.mantis')

@section('title')
Keranjang Pelanggan - Admin View
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Keranjang Pelanggan</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pelanggan</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Tanggal Ditambahkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keranjangs as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->user->nama ?? 'Tidak Diketahui' }}</td>
                            <td>{{ $item->produk->nama_produk ?? 'Produk tidak ditemukan' }}</td>
                            <td>{{ $item->jumlah }}</td>
                            <td>{{ $item->satuan->nama_satuan ?? '-' }}</td>
                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection