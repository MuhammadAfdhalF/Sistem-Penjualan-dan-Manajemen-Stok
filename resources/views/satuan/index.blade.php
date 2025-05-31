@extends('layouts.mantis')

@section('title', 'Data Satuan')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><strong>Data Satuan</strong></li>
@endsection

<head>
    <title>Halaman Satuan Produk</title>
</head>

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Daftar Satuan Produk</h4>
            <a href="{{ route('satuan.create') }}" class="btn btn-primary btn-sm">+ Tambah Satuan</a>
        </div>

        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('satuan.index') }}" class="row gx-2 gy-1 align-items-end flex-nowrap mb-3">
                <div class="col-auto">
                    <label for="filter_produk" class="form-label small mb-1">Nama Produk</label>
                    <select id="filter_produk" name="produk_id" class="form-select form-select-sm" style="min-width:170px;">
                        <option value="">-- Semua Produk --</option>
                        @foreach($daftarProduk as $produk)
                        <option value="{{ $produk->id }}" {{ ($produkId ?? '') == $produk->id ? 'selected' : '' }}>
                            {{ $produk->nama_produk }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-xs px-2 py-1 me-1" style="font-size: 0.8rem;">
                        <i class="ti ti-filter"></i>
                    </button>
                    <a href="{{ route('satuan.index') }}" class="btn btn-secondary btn-xs px-2 py-1" style="font-size: 0.8rem;">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="table">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Nama Satuan</th>
                            <th>Konversi ke Satuan Utama</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($satuans as $index => $satuan)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $satuan->produk->nama_produk ?? '-' }}</td>
                            <td>{{ $satuan->nama_satuan }}</td>
                            <td>{{ $satuan->konversi_ke_satuan_utama }}</td>
                            <td>
                                <a href="{{ route('satuan.edit', $satuan->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>

                                <form action="{{ route('satuan.destroy', $satuan->id) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus satuan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" title="Hapus" type="submit">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data satuan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection