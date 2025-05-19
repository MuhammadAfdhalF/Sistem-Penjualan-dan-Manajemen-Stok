@extends('layouts.mantis')

@section('title', 'Halaman Tambah Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('produk.index') }}" style="opacity: 0.5;">Produk</a></li>
<li class="breadcrumb-item"><strong><a href="{{ route('produk.create') }}">Tambah Data Produk</a></strong></li>
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Form Tambah Data Produk</h4>
            <a href="{{ route('produk.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        <div class="card-body">
            <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk" class="form-control @error('nama_produk') is-invalid @enderror" placeholder="Masukkan nama produk" value="{{ old('nama_produk') }}" required autofocus>
                        @error('nama_produk')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <select name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" required>
                            <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>Pilih kategori</option>
                            <option value="Kebutuhan Rumah Tangga" {{ old('kategori') == 'Kebutuhan Rumah Tangga' ? 'selected' : '' }}>Kebutuhan Rumah Tangga</option>
                            <option value="Bahan Makanan Pokok" {{ old('kategori') == 'Bahan Makanan Pokok' ? 'selected' : '' }}>Bahan Makanan Pokok</option>
                            <option value="Makanan dan Minuman Kemasan" {{ old('kategori') == 'Makanan dan Minuman Kemasan' ? 'selected' : '' }}>Makanan dan Minuman Kemasan</option>
                            <option value="Rokok dan Produk Tembakau" {{ old('kategori') == 'Rokok dan Produk Tembakau' ? 'selected' : '' }}>Rokok dan Produk Tembakau</option>
                        </select>
                        @error('kategori')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" name="stok" id="stok" class="form-control @error('stok') is-invalid @enderror" placeholder="Masukkan stok produk" value="{{ old('stok') }}" min="0" required>
                        @error('stok')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- HAPUS INPUT ROP MANUAL -->

                    <!-- TAMBAH INPUT LEAD TIME, DAILY USAGE, SAFETY STOCK -->
                    <div class="col-md-4 mb-3">
                        <label for="lead_time" class="form-label">Lead Time (hari)</label>
                        <input type="number" name="lead_time" id="lead_time" class="form-control @error('lead_time') is-invalid @enderror" placeholder="Masukkan lead time" value="{{ old('lead_time') }}" min="0" required>
                        @error('lead_time')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="daily_usage" class="form-label">Pemakaian Harian (daily usage)</label>
                        <input type="number" name="daily_usage" id="daily_usage" class="form-control @error('daily_usage') is-invalid @enderror" placeholder="Masukkan pemakaian harian" value="{{ old('daily_usage') }}" min="0" required>
                        @error('daily_usage')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="safety_stock" class="form-label">Safety Stock</label>
                        <input type="number" name="safety_stock" id="safety_stock" class="form-control @error('safety_stock') is-invalid @enderror" placeholder="Masukkan safety stock" value="{{ old('safety_stock') }}" min="0" required>
                        @error('safety_stock')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="satuan" class="form-label">Satuan</label>
                        <select name="satuan" id="satuan" class="form-control @error('satuan') is-invalid @enderror" required>
                            <option value="" disabled {{ old('satuan') ? '' : 'selected' }}>Pilih satuan</option>
                            <option value="bks" {{ old('satuan') == 'bks' ? 'selected' : '' }}>bks</option>
                            <option value="pcs" {{ old('satuan') == 'pcs' ? 'selected' : '' }}>pcs</option>
                            <option value="kg" {{ old('satuan') == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="liter" {{ old('satuan') == 'liter' ? 'selected' : '' }}>liter</option>
                            <option value="box" {{ old('satuan') == 'box' ? 'selected' : '' }}>box</option>
                        </select>
                        @error('satuan')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="harga_normal" class="form-label">Harga Normal</label>
                        <input type="number" name="harga_normal" id="harga_normal" class="form-control @error('harga_normal') is-invalid @enderror" placeholder="Masukkan harga normal" value="{{ old('harga_normal') }}" min="0" required>
                        @error('harga_normal')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="harga_grosir" class="form-label">Harga Grosir</label>
                        <input type="number" name="harga_grosir" id="harga_grosir" class="form-control @error('harga_grosir') is-invalid @enderror" placeholder="Masukkan harga grosir" value="{{ old('harga_grosir') }}" min="0" required>
                        @error('harga_grosir')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror" placeholder="Masukkan deskripsi produk" required>{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="gambar" class="form-label">Gambar Produk</label>
                        <input type="file" name="gambar" id="gambar" class="form-control @error('gambar') is-invalid @enderror" required accept="image/*">
                        @error('gambar')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
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