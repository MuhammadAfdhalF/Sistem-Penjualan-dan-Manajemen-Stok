@extends('layouts.mantis')

@section('title', 'Halaman Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><strong><a href="{{ route('produk.index') }}">Produk</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('produk.create') }}" style="opacity: 0.5;">Tambah Data Produk</a></li>
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Produk</h4>
            <a href="{{ route('produk.create') }}" class="btn btn-primary">Tambah Produk</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Deskripsi</th>
                            <th>Harga Normal</th>
                            <th>Harga Grosir</th>
                            <th>Stok</th>
                            <th>ROP</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produk as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if ($item->gambar)
                                <img src="{{ asset('storage/gambar_produk/' . $item->gambar) }}" alt="Gambar Produk" width="60" height="60" class="rounded-circle object-fit-cover">
                                @else
                                <span class="text-muted">Tidak Ada</span>
                                @endif
                            </td>
                            <td>{{ $item->nama_produk }}</td>
                            <td>{{ $item->deskripsi }}</td>
                            <td>{{ number_format($item->harga_normal, 0, ',', '.') }}</td>
                            <td>{{ number_format($item->harga_grosir, 0, ',', '.') }}</td>
                            <td>
                                {{ $item->stok }}
                                @php
                                $rop = ($item->lead_time * $item->daily_usage) + $item->safety_stock;
                                @endphp
                                @if ($item->stok <= $rop)
                                    <span class="badge bg-danger ms-2" title="Stok sudah mencapai atau di bawah ROP">⚠️ Stok Hampir Habis</span>
                                    @else
                                    <span class="badge bg-success ms-2" title="Stok aman">✓ Aman</span>
                                    @endif
                            </td>
                            <td>{{ $rop }}</td>
                            <td>{{ $item->kategori }}</td>
                            <td>{{ $item->satuan }}</td>
                            <td>
                                <a href="{{ route('produk.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal{{ $item->id }}">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div> <!-- .table-responsive -->
        </div>
    </div>
</div>
@endsection