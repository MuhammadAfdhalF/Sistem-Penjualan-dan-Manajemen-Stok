@extends('layouts.mantis')

@section('title')
Halaman Transaksi Offline
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><strong><a href="{{ route('transaksi_offline.index') }}">Transaksi Offline</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('transaksi_offline.create') }}" style="opacity: 0.5;">Tambah Data Transaksi Offline</a></li>
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Data Transaksi Offline</h4>
            <a href="{{ route('transaksi_offline.create') }}" class="btn btn-primary btn-sm">+ Tambah Transaksi</a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Nama Pelanggan</th>
                            <th>Total</th>
                            <th>Dibayar</th>
                            <th>Kembalian</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->kode_transaksi }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }}</td>
                            <td>{{ $item->pelanggan?->nama ?? 'Bukan Member' }}</td> {{-- ✅ Ambil relasi nama pelanggan --}}

                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->dibayar, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->kembalian, 0, ',', '.') }}</td>
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