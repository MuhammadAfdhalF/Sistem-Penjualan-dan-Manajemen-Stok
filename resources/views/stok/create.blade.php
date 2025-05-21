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
                            @foreach ($produk as $produk)
                            <option value="{{ $produk->id }}" {{ old('produk_id') == $produk->id ? 'selected' : '' }}>
                                {{ $produk->nama_produk }}
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
                            @foreach($satuanBertingkat as $satuan)
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

                            {{-- Input satuan utama --}}
                            <div class="col-md-4 mb-2">
                                <label class="form-label">{{ $produk->satuan_utama }}</label>
                                <input type="number"
                                    class="form-control stok-bertahap-input @error('stok_bertahap.utama') is-invalid @enderror"
                                    name="stok_bertahap[utama]"
                                    data-konversi="1"
                                    min="0"
                                    step="0.01"
                                    value="{{ old('stok_bertahap.utama', $stokBertingkatDefault['utama'] ?? 0) }}">
                                @error('stok_bertahap.utama')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
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
    document.addEventListener("DOMContentLoaded", function() {
        const modeUtama = document.getElementById('mode_utama');
        const modeBertahap = document.getElementById('mode_bertahap');
        const stokUtamaInput = document.getElementById('stok_utama');
        const stokBertingkat = document.getElementById('stokBertingkatInputs');

        if (!modeUtama || !modeBertahap || !stokUtamaInput || !stokBertingkat) return;

        function hitungTotalStok() {
            let total = 0;
            document.querySelectorAll('.stok-bertahap-input').forEach(input => {
                const jumlah = parseInt(input.value) || 0;
                const konversi = parseFloat(input.dataset.konversi) || 0;
                total += jumlah * konversi;
            });
            stokUtamaInput.value = total;
        }

        function toggleInput() {
            if (modeUtama.checked) {
                stokUtamaInput.style.display = 'block';
                stokUtamaInput.disabled = false;

                stokBertingkat.style.display = 'none';
                stokBertingkat.querySelectorAll('input').forEach(input => {
                    input.disabled = true;
                    input.value = ''; // reset input bertahap supaya bersih
                });
            } else {
                stokUtamaInput.style.display = 'none';
                stokUtamaInput.disabled = true;

                stokBertingkat.style.display = 'flex';
                stokBertingkat.querySelectorAll('input').forEach(input => input.disabled = false);
                hitungTotalStok();
            }
        }

        modeUtama.addEventListener('change', toggleInput);
        modeBertahap.addEventListener('change', toggleInput);

        document.querySelectorAll('.stok-bertahap-input').forEach(input => {
            input.addEventListener('input', hitungTotalStok);
        });

        toggleInput(); // Set posisi awal
    });
</script>
@endpush