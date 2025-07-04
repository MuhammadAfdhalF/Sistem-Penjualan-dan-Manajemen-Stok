@extends('layouts.mantis')

@section('title')
Halaman Transaksi Offline
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Penjualan</li>
<li class="breadcrumb-item"><strong><a href="{{ route('transaksi_offline.index') }}">Transaksi Offline</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('transaksi_offline.create') }}" style="opacity: 0.5;">Tambah Data Transaksi Offline</a></li>
@endsection

<head>
    <title>Halaman Transaksi Offline</title>
</head>

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Data Transaksi Offline</h4>
            <a href="{{ route('transaksi_offline.create') }}" class="btn btn-primary btn-sm">+ Tambah Transaksi</a>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('transaksi_offline.index') }}"
                class="row row-cols-1 row-cols-md-auto g-2 mb-3 align-items-end flex-wrap">
                <div class="col">
                    <label for="filter_date" class="form-label small mb-1">Tanggal</label>
                    <input type="date" id="filter_date" name="date" value="{{ request('date') }}"
                        class="form-control form-control-sm" style="min-width: 110px;">
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
                    <select id="filter_year" name="year" class="form-select form-select-sm" style="min-width: 70px;">
                        <option value="">--</option>
                        @foreach(range(now()->year, now()->year-5) as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="filter_pelanggan" class="form-label small mb-1">Pelanggan</label>
                    <select id="filter_pelanggan" name="pelanggan_id" class="form-select form-select-sm">
                        <option value="">-- Semua Pelanggan --</option>
                        @foreach($pelanggans as $p)
                        <option value="{{ $p->id }}" {{ request('pelanggan_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm px-2 py-1" style="font-size: 0.8rem;">
                        <i class="ti ti-filter"></i>
                    </button>
                    <a href="{{ route('transaksi_offline.index') }}" class="btn btn-secondary btn-sm px-2 py-1" style="font-size: 0.8rem;">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Nama Pelanggan</th>
                            <th>Total</th>
                            <th>Metode Pembayaran</th> {{-- Kolom baru --}}
                            <th>Status Pembayaran</th> {{-- Kolom baru --}}
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->kode_transaksi }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }}</td>
                            <td>{{ $item->pelanggan?->nama ?? 'Bukan Member' }}</td>
                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            <td>
                                {{-- Tampilkan metode pembayaran, jika payment_gateway, tampilkan juga payment_type --}}
                                @if ($item->metode_pembayaran === 'payment_gateway')
                                {{ ucwords(str_replace('_', ' ', $item->metode_pembayaran)) }}
                                @if ($item->payment_type)
                                <br><small>({{ ucwords(str_replace('_', ' ', $item->payment_type)) }})</small>
                                @endif
                                @else
                                {{ ucwords(str_replace('_', ' ', $item->metode_pembayaran)) }}
                                @endif
                            </td>
                            <td>
                                @php
                                $badgeClass = '';
                                switch ($item->status_pembayaran) {
                                case 'lunas':
                                $badgeClass = 'bg-success';
                                break;
                                case 'pending':
                                $badgeClass = 'bg-warning';
                                break;
                                case 'gagal':
                                case 'expire':
                                $badgeClass = 'bg-danger';
                                break;
                                default:
                                $badgeClass = 'bg-secondary';
                                break;
                                }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucwords($item->status_pembayaran) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('transaksi_offline.show', $item->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a href="{{ route('transaksi_offline.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal{{ $item->id }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Hapus -->
                        <div class="modal fade" id="confirmDeleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Yakin ingin menghapus transaksi <strong>{{ $item->kode_transaksi }}</strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('transaksi_offline.destroy', $item->id) }}" method="POST">
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
            </div>
        </div>
    </div>
</div>
@endsection