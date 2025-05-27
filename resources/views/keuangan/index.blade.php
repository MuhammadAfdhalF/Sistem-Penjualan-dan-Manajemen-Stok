@extends('layouts.mantis')

@section('title', 'Halaman Keuangan')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen</li>
<li class="breadcrumb-item"><strong><a href="{{ route('keuangan.index') }}">Keuangan</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('keuangan.create') }}" style="opacity: 0.5;">Tambah Catatan Keuangan</a></li>
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Keuangan</h4>
            <a href="{{ route('keuangan.create') }}" class="btn btn-primary">Tambah Keuangan</a>
        </div>

        <form method="GET" action="{{ route('keuangan.index') }}" class="row gx-2 gy-1 align-items-center">
            <div class="col-auto">
                <label for="filter_date" class="form-label small mb-1">Tanggal</label>
                <input type="date" id="filter_date" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
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
            <div class="col-auto">
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
            <div class="col-auto align-self-end">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('keuangan.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>


        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="table">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                            <th>Sumber</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keuangans as $index => $item)
                        <tr class="{{ $item->jenis == 'pengeluaran' ? 'table-warning' : 'table-success' }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $item->tanggal->format('d-m-Y') }}</td>
                            <td class="text-center">
                                <span class="badge {{ $item->jenis == 'pemasukan' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($item->jenis) }}
                                </span>
                            </td>
                            <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td>{{ $item->keterangan ?? '-' }}</td>
                            <td class="text-center">
                                @if($item->sumber === 'offline')
                                <span class="badge bg-primary text-capitalize">{{ $item->sumber }}</span>
                                @elseif($item->sumber === 'online')
                                <span class="badge bg-success text-dark text-capitalize">{{ $item->sumber }}</span>
                                @else
                                <span class="badge bg-secondary text-capitalize">{{ $item->sumber }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->sumber === 'manual')
                                <a href="{{ route('keuangan.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal{{ $item->id }}" title="Hapus">
                                    <i class="ti ti-trash"></i>
                                </button>
                                @else
                                <span class="badge bg-secondary">Otomatis</span>
                                @endif
                            </td>

                        </tr>

                        @if($item->sumber === 'manual')
                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="confirmDeleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmDeleteModalLabel{{ $item->id }}">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus catatan ini sebesar Rp{{ number_format($item->nominal, 0, ',', '.') }}?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('keuangan.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div> <!-- .table-responsive -->

            @php
            $totalPemasukan = $keuangans->where('jenis', 'pemasukan')->sum('nominal');
            $totalPengeluaran = $keuangans->where('jenis', 'pengeluaran')->sum('nominal');
            $totalPemasukanOffline = $keuangans->where('jenis', 'pemasukan')->where('sumber', 'offline')->sum('nominal');
            $totalPemasukanOnline = $keuangans->where('jenis', 'pemasukan')->where('sumber', 'online')->sum('nominal');
            $pemasukanBersih = $totalPemasukan - $totalPengeluaran;
            @endphp

            <div class="table-responsive mt-4">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="fw-semibold px-4">Ringkasan</th>
                            <th class="fw-semibold px-4 text-end">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4">Total Pemasukan Offline</td>
                            <td class="text-end pe-4">Rp {{ number_format($totalPemasukanOffline, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">Total Pemasukan Online</td>
                            <td class="text-end pe-4">Rp {{ number_format($totalPemasukanOnline, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">Total Pemasukan</td>
                            <td class="text-end pe-4">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4">Total Pengeluaran</td>
                            <td class="text-end pe-4">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="fw-semibold table-success">
                            <td class="ps-4">Pemasukan Bersih</td>
                            <td class="text-end pe-4">Rp {{ number_format($pemasukanBersih, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection