@extends('layouts.mantis')

@section('title')
Halaman Pelanggan
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Pelanggan</li>
<li class="breadcrumb-item"><strong><a href="{{ route('pelanggan.index') }}">Pelanggan</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('pelanggan.create') }}" style="opacity: 0.5;">Tambah Data Pelanggan</a></li>
@endsection

<head>
    <title>Halaman Pelanggan</title>
</head>

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Pelanggan</h4>
            <div>
                <a href="{{ route('pelanggan.create') }}" class="btn btn-primary">Tambah Pelanggan</a>
            </div>
        </div>
        <div class="card-body">
            {{-- Tambahkan alert untuk pesan success/error --}}
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Alamat</th>
                            <th>Umur</th> {{-- Label tetap 'Umur' --}}
                            <th>Jenis</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pelanggan as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($item->foto_user)
                                <img src="{{ asset('storage/' . $item->foto_user) }}"
                                    alt="Foto"
                                    width="50"
                                    height="50"
                                    class="rounded-circle"
                                    style="cursor: pointer; object-fit: cover;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#fotoModal{{ $item->id }}">

                                <!-- Modal Foto -->
                                <div class="modal fade" id="fotoModal{{ $item->id }}" tabindex="-1" aria-labelledby="fotoModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-2">
                                                <img src="{{ asset('storage/' . $item->foto_user) }}" alt="Foto {{ $item->nama }}" class="img-fluid rounded">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->no_hp }}</td>
                            <td>{{ $item->alamat }}</td>
                            <td>{{ $item->umur ?? '-' }}</td> {{-- Mengakses 'umur' via accessor, tampilkan '-' jika null --}}
                            <td>{{ $item->jenis_pelanggan }}</td>
                            <td>
                                <a href="{{ route('pelanggan.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit">
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
                                        Apakah Anda yakin ingin menghapus pelanggan {{ $item->nama }}?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('pelanggan.destroy', $item->id) }}" method="POST" style="display:inline;">
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