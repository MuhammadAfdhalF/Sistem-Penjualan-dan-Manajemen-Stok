@extends('layouts.mantis')

@section('title')
Halaman Transaksi Offline
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Transaksi Offline</h4>
            <div>
                <a href="{{ route('transaksi_offline.create') }}" class="btn btn-primary">Tambah Transaksi</a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive"> <!-- Tambahan ini untuk responsif -->
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Dibayar</th>
                            <th>Kembalian</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi as $index => $transaksi)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $transaksi->kode_transaksi }}</td>
                            <td>{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}</td>
                            <td>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($transaksi->dibayar, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('transaksi_offline.show', $transaksi->id) }}" class="btn btn-info btn-sm">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a href="{{ route('transaksi_offline.edit', $transaksi->id) }}" class="btn btn-warning btn-sm">
                                    Edit
                                </a>
                                <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal{{ $transaksi->id }}">
                                    Hapus
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="confirmDeleteModal{{ $transaksi->id }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $transaksi->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmDeleteModalLabel{{ $transaksi->id }}">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus transaksi dengan kode {{ $transaksi->kode_transaksi }}?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('transaksi_offline.destroy', $transaksi->id) }}" method="POST" style="display:inline;">
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