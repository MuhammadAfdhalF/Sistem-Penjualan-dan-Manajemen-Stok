@extends('layouts.mantis')

@section('title')
Halaman Edit Stok
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('stok.index') }}" style="opacity: 0.5;">Stok</a></li>
<li class="breadcrumb-item"><a href="{{ route('stok.create') }}" style="opacity: 0.5;">Tambah Data Stok</a></li>
<li class="breadcrumb-item"><strong><a href="">Edit Data Stok</a></strong></li>
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Form Edit Data Stok</h4>
            <a href="{{ route('stok.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        <div class="card-body">
            <form action="{{ route('stok.update', $stok->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="produk_id" class="form-label">Produk</label>
                        <select name="produk_id" id="produk_id" class="form-control @error('produk_id') is-invalid @enderror">
                            <option value="" disabled {{ old('produk_id', $stok->produk_id) == '' ? 'selected' : '' }}>Pilih Produk</option>
                            @foreach ($produk as $produk)
                            <option value="{{ $produk->id }}" {{ old('produk_id', $stok->produk_id) == $produk->id ? 'selected' : '' }}>
                                {{ $produk->nama_produk }}
                            </option>
                            @endforeach
                        </select>
                        @error('produk_id')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="jenis_disabled" class="form-label">Jenis Stok</label>
                        <select name="jenis_disabled" id="jenis_disabled" class="form-control" disabled>
                            <option value="masuk" {{ $stok->jenis == 'masuk' ? 'selected' : '' }}>Masuk</option>
                            <option value="keluar" {{ $stok->jenis == 'keluar' ? 'selected' : '' }}>Keluar</option>
                        </select>
                        <input type="hidden" name="jenis" value="{{ $stok->jenis }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="jumlah" class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah" class="form-control @error('jumlah') is-invalid @enderror" placeholder="Masukkan jumlah stok" value="{{ old('jumlah', $stok->jumlah) }}">
                        @error('jumlah')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="4" placeholder="Masukkan keterangan stok">{{ old('keterangan', $stok->keterangan) }}</textarea>
                        @error('keterangan')
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