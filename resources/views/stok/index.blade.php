@extends('layouts.mantis')

@section('title')
Halaman Stok
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok </li>
<li class="breadcrumb-item"><strong><a href="{{ route('stok.index') }}">Stok</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('stok.create') }}" style="opacity: 0.5;">Tambah Data Stok</a></li>
@endsection

<head>
    <title>Halaman Stok</title>
</head>

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Stok</h4>
            <div>
                <a href="{{ route('stok.create') }}" class="btn btn-primary">Tambah/Kurangi Stok</a>
            </div>
        </div>
        <div class="card-body">

            <form method="GET" action="{{ route('stok.index') }}"
                class="row row-cols-1 row-cols-md-auto gx-2 gy-1 align-items-end flex-wrap mb-3">
                <div class="col">
                    <label for="filter_date" class="form-label small mb-1">Tanggal</label>
                    <input type="date" id="filter_date" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
                </div>
                <div class="col">
                    <label for="filter_month" class="form-label small mb-1">Bulan</label>
                    <select id="filter_month" name="month" class="form-select form-select-sm" style="min-width: 90px;">
                        <option value="">--</option>
                        @foreach(range(1,12) as $month)
                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($month)->format('M') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="filter_year" class="form-label small mb-1">Tahun</label>
                    <select id="filter_year" name="year" class="form-select form-select-sm" style="min-width: 80px;">
                        <option value="">--</option>
                        @foreach($tahunTersedia as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="filter_produk" class="form-label small mb-1">Produk</label>
                    <select id="filter_produk" name="produk_id" class="form-select form-select-sm" style="min-width: 120px;">
                        <option value="">-- Semua --</option>
                        @foreach($daftarProduk as $p)
                        <option value="{{ $p->id }}" {{ request('produk_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_produk }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="filter_jenis" class="form-label small mb-1">Jenis</label>
                    <select id="filter_jenis" name="jenis" class="form-select form-select-sm" style="min-width: 80px;">
                        <option value="">--</option>
                        <option value="masuk" {{ request('jenis') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                        <option value="keluar" {{ request('jenis') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                    </select>
                </div>
                <div class="col d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-xs px-2 py-1" style="font-size: 0.8rem;">
                        <i class="ti ti-filter"></i>
                    </button>
                    <a href="{{ route('stok.index') }}" class="btn btn-secondary btn-xs px-2 py-1" style="font-size: 0.8rem;">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>



            <div class="table-responsive"> <!-- Tambahkan ini -->
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                            <th>Tanggal</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stok as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->produk->nama_produk }}</td>
                            <td>{{ $item->jenis }}</td>
                            <td>{{ $item->jumlah_bertingkat }}</td>

                            <td>{{ $item->keterangan }}</td>
                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                            <td>
                                <a href="{{ route('stok.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal{{ $item->id }}" title="Hapus">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>

                        </tr>

                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="confirmDeleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmDeleteModalLabel{{ $item->id }}">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus stok untuk produk {{ $item->produk->nama_produk }}?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('stok.destroy', $item->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- Tutup table-responsive -->
        </div>
    </div>
</div>
@endsection