@extends('layouts.mantis')

@section('title')
Halaman Banner
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Konten</li>
<li class="breadcrumb-item"><strong><a href="{{ route('banner.index') }}">Banner</a></strong></li>
<li class="breadcrumb-item"><a href="{{ route('banner.create') }}" style="opacity: 0.5;">Tambah Data Banner</a></li>
@endsection

<head>
    <title>Halaman Banner</title>
</head>

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Data Banner</h4>
            <div>
                <a href="{{ route('banner.create') }}" class="btn btn-primary">Tambah Banner</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Banner</th>
                            <th>Urutan</th>
                            <th>Aktif</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($banners as $index => $banner)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($banner->gambar_url)
                                <img src="{{ asset('storage/' . $banner->gambar_url) }}"
                                    alt="Gambar Banner"
                                    width="80"
                                    height="50"
                                    style="object-fit: contain; border: 1px solid #ddd; cursor: pointer;"
                                    data-bs-toggle="modal" {{-- Tambahan ini --}}
                                    data-bs-target="#gambarModal{{ $banner->id }}"> {{-- Tambahan ini --}}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $banner->nama_banner }}</td>
                            <td>{{ $banner->urutan }}</td>
                            <td>
                                @if($banner->is_aktif)
                                <span class="badge bg-success">Ya</span>
                                @else
                                <span class="badge bg-danger">Tidak</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('banner.edit', $banner->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal{{ $banner->id }}" title="Hapus">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="gambarModal{{ $banner->id }}" tabindex="-1" aria-labelledby="gambarModalLabel{{ $banner->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg"> {{-- Ukuran modal lebih besar --}}
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="gambarModalLabel{{ $banner->id }}">Gambar Banner: {{ $banner->nama_banner }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center p-2">
                                        <img src="{{ asset('storage/' . $banner->gambar_url) }}" alt="Gambar {{ $banner->nama_banner }}" class="img-fluid rounded"> {{-- img-fluid untuk responsif --}}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="confirmDeleteModal{{ $banner->id }}" tabindex="-1" aria-labelledby="confirmDeleteModalLabel{{ $banner->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmDeleteModalLabel{{ $banner->id }}">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus banner **{{ $banner->nama_banner }}**?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('banner.destroy', $banner->id) }}" method="POST" style="display:inline;">
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