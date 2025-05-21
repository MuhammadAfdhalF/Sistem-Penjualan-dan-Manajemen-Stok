@extends('layouts.mantis')

@section('title', 'Harga Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><strong><a href="{{ route('harga_produk.index') }}">Harga Produk</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('harga_produk.create') }}" style="opacity: 0.5;">Tambah Harga</a></li>
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Harga Produk</h4>
            <a href="{{ route('harga_produk.create') }}" class="btn btn-primary">Tambah Harga</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Satuan</th> <!-- Tambahan -->
                            <th>Jenis Pelanggan</th>
                            <th>Harga</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hargaProduk as $index => $harga)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $harga->produk->nama_produk ?? '-' }}</td>
                            <td>{{ $harga->satuan->nama_satuan ?? '-' }}</td> <!-- Tambahan -->

                            <td>
                                <span class="badge {{ $harga->jenis_pelanggan === 'Individu' ? 'bg-success' : 'bg-primary' }}">
                                    {{ $harga->jenis_pelanggan }}
                                </span>
                            </td>

                            <td>Rp {{ number_format($harga->harga, 0, ',', '.') }}</td>

                            <td>
                                <a href="{{ route('harga_produk.edit', $harga->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal{{ $harga->id }}">
                                    Hapus
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="confirmDeleteModal{{ $harga->id }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $harga->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmDeleteModalLabel{{ $harga->id }}">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus harga <strong>{{ $harga->produk->nama_produk }}</strong> tipe <strong>{{ $harga->jenis_pelanggan }}</strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('harga_produk.destroy', $harga->id) }}" method="POST" style="display:inline;">
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
            </div> <!-- .table-responsive -->
        </div>
    </div>
</div>
@endsection