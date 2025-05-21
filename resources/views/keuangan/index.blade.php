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
            <a href="{{ route('keuangan.create') }}" class="btn btn-primary">Tambah Catatan</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                            <th>Relasi Transaksi</th>
                            <th>Sumber</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keuangans as $index => $item)
                        <tr class="{{ $item->jenis == 'pengeluaran' ? 'table-warning' : 'table-success' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->tanggal->format('d-m-Y') }}</td>
                            <td>
                                <span class="badge {{ $item->jenis == 'pemasukan' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($item->jenis) }}
                                </span>
                            </td>
                            <td>Rp{{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td>{{ $item->keterangan ?? '-' }}</td>
                            <td>
                                @if($item->transaksi)
                                <span class="badge bg-info text-dark">#{{ $item->transaksi->kode_transaksi }}</span>
                                @else
                                <span class="text-muted">Manual Input</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary text-capitalize">{{ $item->sumber }}</span>
                            </td>
                            <td>
                                @if($item->sumber === 'manual')
                                <a href="{{ route('keuangan.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal{{ $item->id }}">
                                    Hapus
                                </button>
                                @else
                                <span class="badge bg-secondary">Otomatis</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Konfirmasi Hapus -->
                        @if($item->sumber === 'manual')
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
                                        <form action="{{ route('keuangan.destroy', $item->id) }}" method="POST" style="display:inline;">
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
        </div>
    </div>
</div>
@endsection