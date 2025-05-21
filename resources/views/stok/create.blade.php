@extends('layouts.mantis')

@section('title')
Halaman Tambah/Kurangi Stok
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('stok.index') }}" style="opacity: 0.5;">Stok</a></li>
<li class="breadcrumb-item"><strong><a href="{{ route('stok.create') }}">Tambah Data Stok</a></strong></li>
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Form Tambah Data Stok</h4>
            <a href="{{ route('stok.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        <div class="card-body">
            <form action="{{ route('stok.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="produk_id" class="form-label">Produk</label>
                        <select name="produk_id" id="produk_id" class="form-control @error('produk_id') is-invalid @enderror">
                            <option value="" disabled {{ old('produk_id') ? '' : 'selected' }}>Pilih Produk</option>
                            @foreach ($produk as $item)
                            <option value="{{ $item->id }}" {{ old('produk_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_produk }}
                            </option>
                            @endforeach
                        </select>
                        @error('produk_id')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="jenis" class="form-label">Jenis Stok</label>
                        <select name="jenis" id="jenis" class="form-control @error('jenis') is-invalid @enderror">
                            <option value="" disabled {{ old('jenis') ? '' : 'selected' }}>Pilih Jenis Stok</option>
                            <option value="masuk" {{ old('jenis') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                            <option value="keluar" {{ old('jenis') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                        </select>
                        @error('jenis')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Jumlah Stok (Bertingkat)</label>
                        <div id="stokBertingkatInputs" class="row">
                            {{-- akan diisi oleh JavaScript berdasarkan produk yang dipilih --}}
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="4" placeholder="Masukkan keterangan stok">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const $produkSelect = $('#produk_id');
        const $stokContainer = $('#stokBertingkatInputs');

        function fetchSatuanByProduk(produkId) {
            if (!produkId) return;

            $.ajax({
                url: `/get-satuan-by-produk/${produkId}`,
                method: 'GET',
                success: function(res) {
                    if (res.success && res.data.length > 0) {
                        // Urutkan dari level tertinggi ke terendah
                        const sorted = res.data.sort((a, b) => b.level - a.level);

                        let html = '';
                        sorted.forEach(satuan => {
                            html += `
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">${satuan.nama_satuan}</label>
                                    <input
                                        type="number"
                                        class="form-control stok-bertahap-input"
                                        name="stok_bertahap[${satuan.id}]"
                                        data-konversi="${satuan.konversi_ke_satuan_utama}"
                                        min="0"
                                        step="0.01"
                                        value="0">
                                </div>
                            `;
                        });

                        $stokContainer.html(html);
                    } else {
                        $stokContainer.html('<div class="col-12"><small class="text-muted">Tidak ada satuan ditemukan.</small></div>');
                    }
                },
                error: function() {
                    $stokContainer.html('<div class="col-12 text-danger">Gagal memuat satuan.</div>');
                }
            });
        }

        // Trigger load satuan ketika produk dipilih
        $produkSelect.on('change', function() {
            const produkId = $(this).val();
            fetchSatuanByProduk(produkId);
        });

        // Auto load jika ada produk yang sudah dipilih (misalnya saat gagal validasi)
        if ($produkSelect.val()) {
            fetchSatuanByProduk($produkSelect.val());
        }
    });
</script>
@endpush