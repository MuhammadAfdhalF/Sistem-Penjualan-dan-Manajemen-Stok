@extends('layouts.mantis')

@section('title', 'Edit Satuan Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('satuan.index') }}" style="opacity: 0.5;">Data Satuan</a></li>
<li class="breadcrumb-item"><strong>Edit Satuan</strong></li>
@endsection

<head>
     <title>Halaman Edit Satuan Produk</title>
</head>

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Form Edit Satuan</h4>
            <a href="{{ route('satuan.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        <div class="card-body">
            <form action="{{ route('satuan.update', $satuan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Pilih Produk --}}
                    <div class="col-md-6 mb-3">
                        <label for="produk_id" class="form-label">Produk</label>
                        <select name="produk_id" id="produk_id" class="form-control @error('produk_id') is-invalid @enderror" required>
                            <option value="" disabled {{ old('produk_id', $satuan->produk_id) == '' ? 'selected' : '' }}>Pilih Produk</option>
                            @foreach($produks as $produk)
                            <option value="{{ $produk->id }}" {{ old('produk_id', $satuan->produk_id) == $produk->id ? 'selected' : '' }}>
                                {{ $produk->nama_produk }}
                            </option>
                            @endforeach
                        </select>
                        @error('produk_id')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Nama Satuan --}}
                    <div class="col-md-6 mb-3">
                        <label for="nama_satuan" class="form-label">Nama Satuan</label>
                        <input type="text" name="nama_satuan" id="nama_satuan"
                            class="form-control @error('nama_satuan') is-invalid @enderror"
                            value="{{ old('nama_satuan', $satuan->nama_satuan) }}" required>
                        @error('nama_satuan')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Konversi ke Satuan Utama --}}
                    <div class="col-md-6 mb-3">
                        <label for="konversi_ke_satuan_utama" class="form-label">Konversi ke Satuan Utama</label>
                        <input type="number" step="0.0001" name="konversi_ke_satuan_utama" id="konversi_ke_satuan_utama"
                            class="form-control @error('konversi_ke_satuan_utama') is-invalid @enderror"
                            value="{{ old('konversi_ke_satuan_utama', $satuan->konversi_ke_satuan_utama) }}" required>
                        @error('konversi_ke_satuan_utama')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Level --}}
                    <div class="col-md-6 mb-3">
                        <label for="level" class="form-label">Level (1 sampai 5)</label>
                        <input type="number" name="level" id="level" min="1" max="5"
                            class="form-control @error('level') is-invalid @enderror"
                            value="{{ old('level', $satuan->level) }}" required>
                        @error('level')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection