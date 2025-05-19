@extends('layouts.mantis')

@section('title', 'Halaman Edit Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('produk.index') }}" style="opacity: 0.5;">Produk</a></li>
<li class="breadcrumb-item"><a href="{{ route('produk.create') }}" style="opacity: 0.5;">Tambah Data Produk</a></li>
<li class="breadcrumb-item"><strong>Edit Data Produk</strong></li>
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Form Edit Data Produk</h4>
            <a href="{{ route('produk.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        <div class="card-body">
            <form action="{{ route('produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Nama Produk --}}
                    <div class="col-md-6 mb-3">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk"
                            class="form-control @error('nama_produk') is-invalid @enderror"
                            placeholder="Masukkan nama produk"
                            value="{{ old('nama_produk', $produk->nama_produk) }}" required autofocus>
                        @error('nama_produk')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Kategori --}}
                    <div class="col-md-6 mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" required>
                            <option value="" disabled {{ old('kategori', $produk->kategori) ? '' : 'selected' }}>Pilih kategori</option>
                            <option value="Kebutuhan Rumah Tangga" {{ old('kategori', $produk->kategori) == 'Kebutuhan Rumah Tangga' ? 'selected' : '' }}>Kebutuhan Rumah Tangga</option>
                            <option value="Bahan Makanan Pokok" {{ old('kategori', $produk->kategori) == 'Bahan Makanan Pokok' ? 'selected' : '' }}>Bahan Makanan Pokok</option>
                            <option value="Makanan dan Minuman Kemasan" {{ old('kategori', $produk->kategori) == 'Makanan dan Minuman Kemasan' ? 'selected' : '' }}>Makanan dan Minuman Kemasan</option>
                            <option value="Rokok dan Produk Tembakau" {{ old('kategori', $produk->kategori) == 'Rokok dan Produk Tembakau' ? 'selected' : '' }}>Rokok dan Produk Tembakau</option>
                        </select>
                        @error('kategori')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Stok --}}
                    <div class="col-md-6 mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" name="stok" id="stok"
                            class="form-control @error('stok') is-invalid @enderror"
                            placeholder="Masukkan stok produk"
                            value="{{ old('stok', $produk->stok) }}" min="0" required>
                        @error('stok')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Lead Time --}}
                    <div class="col-md-4 mb-3">
                        <label for="lead_time" class="form-label">Lead Time (hari)</label>
                        <input type="number" name="lead_time" id="lead_time"
                            class="form-control @error('lead_time') is-invalid @enderror"
                            placeholder="Masukkan lead time"
                            value="{{ old('lead_time', $produk->lead_time) }}" min="0" required>
                        @error('lead_time')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Safety Stock --}}
                    <div class="col-md-4 mb-3">
                        <label for="safety_stock" class="form-label">Safety Stock</label>
                        <input type="number" name="safety_stock" id="safety_stock"
                            class="form-control @error('safety_stock') is-invalid @enderror"
                            placeholder="Masukkan safety stock"
                            value="{{ old('safety_stock', $produk->safety_stock) }}" min="0" required>
                        @error('safety_stock')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Satuan --}}
                    <div class="col-md-6 mb-3">
                        <label for="satuan_utama" class="form-label">satuan_utama</label>
                        <select name="satuan_utama" id="satuan_utama" class="form-control @error('satuan_utama') is-invalid @enderror" required>
                            <option value="" disabled {{ old('satuan_utama', $produk->satuan_utama) ? '' : 'selected' }}>Pilih satuan_utama</option>
                            <option value="bks" {{ old('satuan_utama', $produk->satuan_utama) == 'bks' ? 'selected' : '' }}>bks</option>
                            <option value="pcs" {{ old('satuan_utama', $produk->satuan_utama) == 'pcs' ? 'selected' : '' }}>pcs</option>
                            <option value="kg" {{ old('satuan_utama', $produk->satuan_utama) == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="liter" {{ old('satuan_utama', $produk->satuan_utama) == 'liter' ? 'selected' : '' }}>liter</option>
                            <option value="box" {{ old('satuan_utama', $produk->satuan_utama) == 'box' ? 'selected' : '' }}>box</option>
                        </select>
                        @error('satuan_utama')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Harga Normal --}}
                    <div class="col-md-6 mb-3">
                        <label for="harga_normal" class="form-label">Harga Normal</label>
                        <input type="number" name="harga_normal" id="harga_normal"
                            class="form-control @error('harga_normal') is-invalid @enderror"
                            placeholder="Masukkan harga normal"
                            value="{{ old('harga_normal', (int) $produk->harga_normal) }}" min="0" required>
                        @error('harga_normal')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Harga Grosir --}}
                    <div class="col-md-6 mb-3">
                        <label for="harga_grosir" class="form-label">Harga Grosir</label>
                        <input type="number" name="harga_grosir" id="harga_grosir"
                            class="form-control @error('harga_grosir') is-invalid @enderror"
                            placeholder="Masukkan harga grosir"
                            value="{{ old('harga_grosir', (int) ($produk->harga_grosir ?? 0)) }}" min="0" required>
                        @error('harga_grosir')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="col-12 mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4"
                            class="form-control @error('deskripsi') is-invalid @enderror"
                            placeholder="Masukkan deskripsi produk" required>{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                        @error('deskripsi')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Gambar Produk --}}
                    <div class="col-12 mb-3">
                        <label for="gambar" class="form-label">Gambar Produk</label><br>
                        @if ($produk->gambar)
                        <img src="{{ asset('storage/gambar_produk/' . $produk->gambar) }}" width="120" class="mb-2 rounded" alt="Gambar Produk">
                        @endif
                        <input type="file" name="gambar" id="gambar" class="form-control @error('gambar') is-invalid @enderror" accept="image/*">
                        @error('gambar')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Hidden daily_usage (jika tetap ingin disimpan) --}}
                    <input type="hidden" name="daily_usage" value="{{ $produk->daily_usage }}">
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection