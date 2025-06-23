@extends('layouts.mantis')

@section('title')
Halaman Tambah Banner
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Konten</li>
<li class="breadcrumb-item"><a href="{{ route('banner.index') }}" style="opacity: 0.5;">Banner</a></li>
<li class="breadcrumb-item"><strong><a href="{{ route('banner.create') }}">Tambah Data Banner</a></strong></li>
@endsection

<head>
    <title>Halaman Tambah Banner</title>
</head>

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Form Tambah Data Banner</h4>
            <a href="{{ route('banner.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        <div class="card-body">
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

            <form action="{{ route('banner.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_banner" class="form-label">Nama Banner</label>
                        <input type="text" name="nama_banner" id="nama_banner" class="form-control @error('nama_banner') is-invalid @enderror" placeholder="Masukkan nama banner" value="{{ old('nama_banner') }}" autofocus>
                        @error('nama_banner')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="urutan" class="form-label">Urutan (Opsional)</label>
                        <input type="number" name="urutan" id="urutan" class="form-control @error('urutan') is-invalid @enderror" placeholder="Masukkan angka urutan banner (misal: 1, 2, 3)" value="{{ old('urutan', 0) }}">
                        @error('urutan')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="gambar_url" class="form-label">Gambar Banner</label>
                        <input type="file" name="gambar_url" id="gambar_url" class="form-control @error('gambar_url') is-invalid @enderror">
                        <small class="form-text text-muted">Format: JPEG, PNG, JPG, GIF, SVG. Max: 2MB.</small>
                        @error('gambar_url')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" id="is_aktif" name="is_aktif" role="switch" value="1" {{ old('is_aktif', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_aktif">Aktifkan Banner</label>
                            @error('is_aktif')
                            <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group text-end">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection