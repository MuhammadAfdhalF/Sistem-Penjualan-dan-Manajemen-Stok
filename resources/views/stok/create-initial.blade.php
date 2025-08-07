@extends('layouts.mantis')

@section('title', 'Tambah Stok Awal: ' . $produk->nama_produk)

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('produk.index') }}">Produk</a></li>
<li class="breadcrumb-item"><strong>Tambah Stok Awal</strong></li>
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Tahap 2/2: Tambah Stok Awal untuk **{{ $produk->nama_produk }}**</h4>
            <a href="{{ route('produk.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        <div class="card-body">
            <form action="{{ route('stok.storeInitialStok') }}" method="POST">
                @csrf
                <input type="hidden" name="produk_id" value="{{ $produk->id }}">

                <h5 class="mb-3">Jumlah Stok Berdasarkan Satuan</h5>
                <div class="row g-3">
                    @forelse ($satuans as $satuan)
                    <div class="col-md-6 col-lg-4">
                        <label for="stok_awal_{{ $satuan->id }}" class="form-label">{{ $satuan->nama_satuan }}</label>
                        <input type="number" name="stok_awal[{{ $satuan->id }}][jumlah]" id="stok_awal_{{ $satuan->id }}" class="form-control" placeholder="Jumlah" min="0">
                        <input type="hidden" name="stok_awal[{{ $satuan->id }}][satuan_id]" value="{{ $satuan->id }}">
                    </div>
                    @empty
                    <div class="col-12">
                        <p class="text-muted">Tidak ada satuan ditemukan untuk produk ini. Harap tambahkan satuan terlebih dahulu.</p>
                    </div>
                    @endforelse
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Stok Awal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection