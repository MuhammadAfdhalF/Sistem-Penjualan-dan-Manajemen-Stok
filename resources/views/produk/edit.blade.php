@extends('layouts.mantis')

@section('title', 'Halaman Edit Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('produk.index') }}" style="opacity: 0.5;">Produk</a></li>
<li class="breadcrumb-item"><a href="{{ route('produk.create') }}" style="opacity: 0.5;">Tambah Data Produk</a></li>
<li class="breadcrumb-item"><strong>Edit Data Produk</strong></li>
@endsection

<head>
     <title>Halaman Edit Produk</title>
</head>

@section('content')
@php
$modeStok = old('mode_stok') ?? (count($stokBertingkatDefault) > 0 ? 'bertahap' : 'utama');
@endphp


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
                            value="{{ old('nama_produk', $produk->nama_produk) }}" required autofocus>
                        @error('nama_produk') <small class="text-danger">{{ $message }}</small> @enderror
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
                        @error('kategori') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Input Stok --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Input Stok</label>

                        {{-- Pilihan mode --}}
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="mode_stok" id="mode_utama" value="utama"
                                {{ $modeStok === 'utama' ? 'checked' : '' }}>
                            <label class="form-check-label" for="mode_utama">
                                Input langsung satuan terkecil
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="mode_stok" id="mode_bertahap" value="bertahap"
                                {{ $modeStok === 'bertahap' ? 'checked' : '' }}>
                            <label class="form-check-label" for="mode_bertahap">
                                Input stok berdasarkan satuan bertingkat
                            </label>
                        </div>

                        {{-- Input stok satuan utama --}}
                        <input type="number" id="stok_utama" class="form-control mt-2"
                            value="{{ old('stok', $produk->stok) }}" min="0">

                        {{-- Hidden final stok --}}
                        <input type="hidden" id="stok_final_hidden">

                        {{-- Input stok bertingkat --}}
                        <div id="stokBertingkatInputs" class="row mt-2" style="display: none;">
                            @foreach($satuanBertingkat as $satuan)
                            <div class="col-md-6 mt-2">
                                <label class="form-label">{{ $satuan->nama_satuan }}</label>
                                <input type="number"
                                    class="form-control stok-bertahap-input"
                                    name="stok_bertahap[{{ $satuan->id }}]"
                                    data-konversi="{{ $satuan->konversi_ke_satuan_utama }}"
                                    min="0"
                                    value="{{ old('stok_bertahap.' . $satuan->id, $stokBertingkatDefault[$satuan->id] ?? 0) }}">
                            </div>
                            @endforeach
                        </div>



                        {{-- Lead Time --}}
                        <div class="col-md-4 mb-3">
                            <label for="lead_time" class="form-label">Lead Time (hari)</label>
                            <input type="number" name="lead_time" id="lead_time"
                                class="form-control @error('lead_time') is-invalid @enderror"
                                value="{{ old('lead_time', $produk->lead_time) }}" min="0" required>
                            @error('lead_time') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>


                        {{-- Deskripsi --}}
                        <div class="col-12 mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="4"
                                class="form-control @error('deskripsi') is-invalid @enderror"
                                required>{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                            @error('deskripsi') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Gambar --}}
                        <div class="col-12 mb-3">
                            <label for="gambar" class="form-label">Gambar Produk</label><br>
                            @if ($produk->gambar)
                            <img src="{{ asset('storage/gambar_produk/' . $produk->gambar) }}" width="120" class="mb-2 rounded" alt="Gambar Produk">
                            @endif
                            <input type="file" name="gambar" id="gambar" class="form-control @error('gambar') is-invalid @enderror" accept="image/*">
                            @error('gambar') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

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

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modeUtama = document.getElementById('mode_utama');
        const modeBertahap = document.getElementById('mode_bertahap');

        const stokUtamaInput = document.getElementById('stok_utama');
        const stokFinalInput = document.getElementById('stok_final_hidden');
        const stokBertingkat = document.getElementById('stokBertingkatInputs');

        const safetyStockUtamaInput = document.getElementById('safety_stock');
        const safetyStockFinalInput = document.getElementById('safety_stock_final_hidden');
        const safetyStockUtamaWrapper = document.getElementById('safetyStockUtamaWrapper');
        const safetyStockBertingkatWrapper = document.getElementById('safetyStockBertingkatWrapper');

        function hitungTotalStok() {
            let total = 0;
            document.querySelectorAll('.stok-bertahap-input').forEach(input => {
                const jumlah = parseFloat(input.value) || 0;
                const konversi = parseFloat(input.dataset.konversi) || 1;
                total += jumlah * konversi;
            });
            if (stokFinalInput) {
                stokFinalInput.value = total.toFixed(2);
            }
        }

        function hitungTotalSafetyStock() {
            let total = 0;
            document.querySelectorAll('.safety-stock-bertahap-input').forEach(input => {
                const jumlah = parseFloat(input.value) || 0;
                const konversi = parseFloat(input.dataset.konversi) || 1;
                total += jumlah * konversi;
            });
            if (safetyStockFinalInput) {
                safetyStockFinalInput.value = total.toFixed(2);
            }
        }

        function toggleInput() {
            const isUtama = modeUtama.checked;

            // Stok
            stokUtamaInput.style.display = isUtama ? 'block' : 'none';
            stokUtamaInput.disabled = !isUtama;
            stokUtamaInput.name = isUtama ? 'stok' : '';
            stokBertingkat.style.display = isUtama ? 'none' : 'flex';
            stokBertingkat.querySelectorAll('input').forEach(input => input.disabled = isUtama);
            stokFinalInput.name = isUtama ? '' : 'stok';
            if (isUtama) {
                stokFinalInput.value = parseFloat(stokUtamaInput.value || 0).toFixed(2);
            } else {
                hitungTotalStok();
            }

            // Safety stock
            safetyStockUtamaWrapper.style.display = isUtama ? 'block' : 'none';
            safetyStockUtamaInput.disabled = !isUtama;
            safetyStockUtamaInput.name = isUtama ? 'safety_stock' : '';
            safetyStockBertingkatWrapper.style.display = isUtama ? 'none' : 'block';
            document.querySelectorAll('.safety-stock-bertahap-input').forEach(input => input.disabled = isUtama);
            safetyStockFinalInput.name = isUtama ? '' : 'safety_stock';
            if (isUtama) {
                safetyStockFinalInput.value = parseFloat(safetyStockUtamaInput.value || 0).toFixed(2);
            } else {
                hitungTotalSafetyStock();
            }
        }

        modeUtama.addEventListener('change', toggleInput);
        modeBertahap.addEventListener('change', toggleInput);

        stokUtamaInput.addEventListener('input', () => {
            if (modeUtama.checked) {
                stokFinalInput.value = parseFloat(stokUtamaInput.value || 0).toFixed(2);
            }
        });

        safetyStockUtamaInput.addEventListener('input', () => {
            if (modeUtama.checked) {
                safetyStockFinalInput.value = parseFloat(safetyStockUtamaInput.value || 0).toFixed(2);
            }
        });

        document.querySelectorAll('.stok-bertahap-input').forEach(input => {
            input.addEventListener('input', hitungTotalStok);
        });

        document.querySelectorAll('.safety-stock-bertahap-input').forEach(input => {
            input.addEventListener('input', hitungTotalSafetyStock);
        });

        toggleInput(); // initial
    });
</script>

@endpush