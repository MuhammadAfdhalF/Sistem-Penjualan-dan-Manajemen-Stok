@extends('layouts.mantis')

@section('title')
Halaman Transaksi Online
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Penjualan</li>
<li class="breadcrumb-item"><strong><a href="{{ route('transaksi_online.index') }}">Transaksi Online</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('transaksi_online.create') }}" style="opacity: 0.5;">Tambah Transaksi</a></li>
@endsection

<head>
    <title>Halaman Transaksi Online</title>
</head>

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Data Transaksi Online</h4>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('transaksi_online.index') }}" class="row g-2 align-items-end mb-3">
                {{-- Tanggal --}}
                <div class="col-12 col-md-auto">
                    <label for="filter_date" class="form-label small mb-1">Tanggal</label>
                    <input type="date" id="filter_date" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
                </div>

                {{-- Bulan --}}
                <div class="col-6 col-md-auto">
                    <label for="filter_month" class="form-label small mb-1">Bulan</label>
                    <select id="filter_month" name="month" class="form-select form-select-sm">
                        <option value="">-- Semua Bulan --</option>
                        @foreach(range(1,12) as $month)
                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tahun --}}
                <div class="col-6 col-md-auto">
                    <label for="filter_year" class="form-label small mb-1">Tahun</label>
                    <select id="filter_year" name="year" class="form-select form-select-sm">
                        <option value="">-- Semua Tahun --</option>
                        @foreach(range(date('Y'), date('Y') - 5) as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Pelanggan --}}
                <div class="col-12 col-md-auto">
                    <label for="filter_user" class="form-label small mb-1">Pelanggan</label>
                    <select id="filter_user" name="user_id" class="form-select form-select-sm">
                        <option value="">-- Semua Pelanggan --</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Metode Pembayaran (BARU DITAMBAHKAN) --}}
                <div class="col-12 col-md-auto">
                    <label for="filter_metode_pembayaran" class="form-label small mb-1">Metode Pembayaran</label>
                    <select id="filter_metode_pembayaran" name="metode_pembayaran" class="form-select form-select-sm">
                        <option value="">-- Semua Metode --</option>
                        <option value="payment_gateway" {{ ($filterMetodePembayaran ?? '') == 'payment_gateway' ? 'selected' : '' }}>Payment Gateway</option>
                        <option value="cod" {{ ($filterMetodePembayaran ?? '') == 'cod' ? 'selected' : '' }}>COD</option>
                        <option value="bayar_di_toko" {{ ($filterMetodePembayaran ?? '') == 'bayar_di_toko' ? 'selected' : '' }}>Bayar di Toko</option>
                    </select>
                </div>
                
                {{-- Metode Pengambilan --}}
                <div class="col-12 col-md-auto">
                    <label for="filter_metode_pengambilan" class="form-label small mb-1">Metode Pengambilan</label>
                    <select id="filter_metode_pengambilan" name="metode_pengambilan" class="form-select form-select-sm">
                        <option value="">-- Semua Metode --</option>
                        <option value="ambil di toko" {{ request('metode_pengambilan') == 'ambil di toko' ? 'selected' : '' }}>Ambil di Toko</option>
                        <option value="diantar" {{ request('metode_pengambilan') == 'diantar' ? 'selected' : '' }}>Diantar</option>
                    </select>
                </div>

                {{-- Tombol Aksi --}}
                <div class="col-12 col-md-auto d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm px-3 py-1">
                        <i class="ti ti-filter"></i>
                    </button>
                    <a href="{{ route('transaksi_online.index') }}" class="btn btn-secondary btn-sm px-3 py-1">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Nama Pelanggan</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status Transaksi</th>
                            <th>Metode Pengambilan</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksis as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->kode_transaksi }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }}</td>
                            <td>
                                {{ $item->user ? $item->user->nama . ' (' . $item->user->jenis_pelanggan . ')' : '-' }}
                            </td>
                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            <td>
                                @php
                                $statusPembayaran = $item->status_pembayaran;
                                $metodePembayaran = $item->metode_pembayaran;
                                $paymentType = $item->payment_type;

                                $badgeClass = '';
                                switch ($statusPembayaran) {
                                    case 'lunas':
                                        $badgeClass = 'bg-success';
                                        break;
                                    case 'pending':
                                        $badgeClass = 'bg-warning';
                                        break;
                                    case 'gagal':
                                        $badgeClass = 'bg-danger';
                                        break;
                                    default:
                                        $badgeClass = 'bg-secondary';
                                        break;
                                }
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($statusPembayaran) }}
                                </span>
                                @if($metodePembayaran === 'payment_gateway' && $paymentType)
                                <br><small>({{ ucwords(str_replace('_', ' ', $paymentType)) }})</small>
                                @endif
                            </td>
                            <td>
                                @if($item->status_transaksi == 'diproses')
                                <span class="badge bg-warning text-dark">Sedang Diproses</span>
                                @elseif($item->status_transaksi == 'diantar')
                                <span class="badge bg-info text-dark">Sedang Diantar</span>
                                @elseif($item->status_transaksi == 'diambil')
                                <span class="badge bg-primary">Silahkan Diambil</span>
                                @elseif($item->status_transaksi == 'selesai')
                                <span class="badge bg-success">Pesanan Selesai</span>
                                @elseif($item->status_transaksi == 'batal')
                                <span class="badge bg-danger">{{ ucfirst($item->status_transaksi) }}</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($item->status_transaksi) }}</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($item->metode_pengambilan) }}</td>
                            <td>
                                <a href="{{ route('transaksi_online.show', $item->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a href="{{ route('transaksi_online.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal{{ $item->id }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>

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
                                        <form action="{{ route('transaksi_online.destroy', $item->id) }}" method="POST">
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