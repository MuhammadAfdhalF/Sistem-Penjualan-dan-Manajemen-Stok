@extends('layouts.mantis')

@section('title')
Halaman Dashboard
@endsection


@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard Monitoring Stok</h1>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nama Produk</th>
                <th>Stok</th>
                <th>ROP</th>
                <th>Lead Time (hari)</th>
                <th>Daily Usage</th>
                <th>Safety Stock</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($produk as $produk)
            <tr class="{{ $produk->isStokDiBawahROP() ? 'table-danger' : '' }}">
                <td>{{ $produk->nama_produk }}</td>
                <td>{{ $produk->stok }}</td>
                <td>{{ $produk->rop }}</td>
                <td>{{ $produk->lead_time ?? '-' }}</td>
                <td>{{ $produk->daily_usage ?? '-' }}</td>
                <td>{{ $produk->safety_stock ?? '-' }}</td>
                <td>
                    @if($produk->isStokDiBawahROP())
                    <span class="badge bg-danger">
                        Butuh Reorder Min: {{ ($produk->rop - $produk->stok) + 1 }}
                    </span>
                    @else
                    <span class="badge bg-success">Stok Aman</span>
                    @endif
                </td>


            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data produk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection