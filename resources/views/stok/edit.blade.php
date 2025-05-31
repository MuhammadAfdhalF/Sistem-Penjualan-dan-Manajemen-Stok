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

<head>
     <title>Halaman Edit Stok</title>
</head>

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
                            @foreach ($produk as $item)
                            <option value="{{ $item->id }}" {{ old('produk_id', $stok->produk_id) == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_produk }}
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

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Jumlah Stok (Bertingkat)</label>
                        <div id="stokBertingkatInputs" class="row">
                            @foreach($satuanBertingkat->sortByDesc('level') as $satuan)
                            <div class="col-md-4 mb-2">
                                <label class="form-label">{{ $satuan->nama_satuan }}</label>
                                <input type="number"
                                    class="form-control stok-bertahap-input @error('stok_bertahap.' . $satuan->id) is-invalid @enderror"
                                    name="stok_bertahap[{{ $satuan->id }}]"
                                    data-konversi="{{ $satuan->konversi_ke_satuan_utama }}"
                                    min="0"
                                    step="0.01"
                                    value="{{ old('stok_bertahap.' . $satuan->id, $stokBertingkatDefault[$satuan->id] ?? 0) }}">
                                @error('stok_bertahap.' . $satuan->id)
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            @endforeach
                        </div>
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

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const produkSelect = document.getElementById('produk_id');
        const stokInputsWrapper = document.getElementById('stokBertingkatInputs');

        produkSelect.addEventListener('change', function() {
            const produkId = this.value;
            stokInputsWrapper.innerHTML = '<div class="text-muted">Memuat satuan...</div>';

            fetch(`/get-satuan-by-produk/${produkId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        stokInputsWrapper.innerHTML = '';
                        data.data.sort((a, b) => b.level - a.level); // dari tinggi ke rendah

                        data.data.forEach(satuan => {
                            const div = document.createElement('div');
                            div.classList.add('col-md-4', 'mb-2');
                            div.innerHTML = `
                                <label class="form-label">${satuan.nama_satuan}</label>
                                <input type="number" class="form-control stok-bertahap-input" 
                                       name="stok_bertahap[${satuan.id}]" 
                                       data-konversi="${satuan.konversi_ke_satuan_utama}" 
                                       min="0" step="0.01" value="0">
                            `;
                            stokInputsWrapper.appendChild(div);
                        });
                    } else {
                        stokInputsWrapper.innerHTML = '<div class="text-danger">Tidak ada satuan tersedia untuk produk ini.</div>';
                    }
                })
                .catch(error => {
                    console.error('Gagal memuat satuan:', error);
                    stokInputsWrapper.innerHTML = '<div class="text-danger">Terjadi kesalahan saat memuat satuan.</div>';
                });
        });
    });
</script>
@endpush