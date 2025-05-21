@extends('layouts.mantis')

@section('title', 'Edit Harga Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('harga_produk.index') }}">Harga Produk</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Edit Harga Produk</h4>
        <a href="{{ route('harga_produk.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <form action="{{ route('harga_produk.update', $hargaProduk->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="produk_id" class="form-label">Produk</label>
                <select name="produk_id" id="produk_id" class="form-control @error('produk_id') is-invalid @enderror">
                    <option value="">-- Pilih Produk --</option>
                    @foreach ($produk as $item)
                    <option value="{{ $item->id }}" {{ old('produk_id', $hargaProduk->produk_id) == $item->id ? 'selected' : '' }}>
                        {{ $item->nama_produk }}
                    </option>
                    @endforeach
                </select>
                @error('produk_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="satuan_id" class="form-label">Satuan</label>
                <select name="satuan_id" id="satuan_id" class="form-control @error('satuan_id') is-invalid @enderror">
                    <option value="">-- Pilih Produk Terlebih Dahulu --</option>
                </select>
                @error('satuan_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="jenis_pelanggan" class="form-label">Jenis Pelanggan</label>
                <select name="jenis_pelanggan" id="jenis_pelanggan" class="form-control @error('jenis_pelanggan') is-invalid @enderror">
                    <option value="">-- Pilih Jenis Pelanggan --</option>
                    <option value="Toko Kecil" {{ old('jenis_pelanggan', $hargaProduk->jenis_pelanggan) == 'Toko Kecil' ? 'selected' : '' }}>Toko Kecil</option>
                    <option value="Individu" {{ old('jenis_pelanggan', $hargaProduk->jenis_pelanggan) == 'Individu' ? 'selected' : '' }}>Individu</option>
                </select>
                @error('jenis_pelanggan')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="harga_display" class="form-label">Harga</label>
                <input type="text" id="harga_display" class="form-control" placeholder="Contoh: 36.000">
                <input type="hidden" name="harga" id="harga_hidden" value="{{ old('harga', $hargaProduk->harga) }}">
                @error('harga')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Perbarui</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Load satuan berdasarkan produk yang dipilih
        $('#produk_id').on('change', function() {
            const produkId = $(this).val();
            const satuanIdTerpilih = `{{ old('satuan_id', $hargaProduk->satuan_id) }}`;
            const $satuanSelect = $('#satuan_id');

            $satuanSelect.empty().append('<option value="">Memuat...</option>');

            if (!produkId) {
                $satuanSelect.html('<option value="">-- Pilih Satuan --</option>');
                return;
            }

            $.ajax({
                url: `/get-satuan-by-produk/${produkId}`,
                method: 'GET',
                success: function(res) {
                    if (res.success && res.data.length > 0) {
                        $satuanSelect.empty().append('<option value="">-- Pilih Satuan --</option>');
                        res.data.forEach(item => {
                            const selected = item.id == satuanIdTerpilih ? 'selected' : '';
                            $satuanSelect.append(`<option value="${item.id}" ${selected}>${item.nama_satuan} (${item.konversi_ke_satuan_utama})</option>`);
                        });
                    } else {
                        $satuanSelect.html('<option value="">Tidak ada satuan tersedia</option>');
                    }
                },
                error: function() {
                    $satuanSelect.html('<option value="">Gagal memuat satuan</option>');
                }
            });
        });

        // Trigger load awal
        $('#produk_id').trigger('change');

        // Format harga display
        const $displayHarga = $('#harga_display');
        const $hargaHidden = $('#harga_hidden');

        function formatRupiah(angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function unformatRupiah(str) {
            return str.replace(/\./g, "");
        }

        // Set awal harga display
        if ($hargaHidden.val()) {
            $displayHarga.val(formatRupiah($hargaHidden.val()));
        }

        $displayHarga.on('input', function() {
            const bersih = unformatRupiah($(this).val());
            $hargaHidden.val(bersih);
            $(this).val(formatRupiah(bersih));
        });
    });
</script>
@endpush