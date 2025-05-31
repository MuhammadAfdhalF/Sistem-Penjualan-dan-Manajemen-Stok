@extends('layouts.mantis')

@section('title', 'Halaman Tambah Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('produk.index') }}" style="opacity: 0.5;">Produk</a></li>
<li class="breadcrumb-item"><strong><a href="{{ route('produk.create') }}">Tambah Data Produk</a></strong></li>
@endsection

<head>
     <title>Halaman Tambah Produk</title>
</head>

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

                    {{-- Nama Produk --}}
                    <div class="col-md-6 mb-3">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk" class="form-control @error('nama_produk') is-invalid @enderror" placeholder="Masukkan nama produk" value="{{ old('nama_produk') }}" required autofocus>
                        @error('nama_produk')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Kategori --}}
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

                    {{-- Pilihan mode stok --}}
                    <div class="col-md-12 mb-2">
                        <label for="mode_stok" class="form-label">Tipe Stok</label>
                        <select name="mode_stok" id="mode_stok" class="form-select">
                            <option value="utama" {{ old('mode_stok', 'utama') == 'utama' ? 'selected' : '' }}>Stok Utama (satuan biasa)</option>
                            <option value="bertahap" {{ old('mode_stok') == 'bertahap' ? 'selected' : '' }}>Stok Bertahap (multi satuan)</option>
                        </select>
                    </div>

                    {{-- Input stok biasa --}}
                    <div id="stok-biasa-wrapper" class="col-md-6 mb-3" style="{{ old('mode_stok', 'utama') == 'utama' ? 'display:block' : 'display:none' }}">
                        <label for="stok" class="form-label">📦 Stok</label>
                        <input type="number" name="stok" id="stok"
                            class="form-control @error('stok') is-invalid @enderror"
                            placeholder="🧮 Masukkan jumlah stok"
                            value="{{ old('stok', 0) }}" min="0">
                        @error('stok')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>


                    {{-- Input stok bertingkat --}}
                    <div id="stok-bertingkat-input-wrapper" class="col-md-12" style="{{ old('mode_stok') == 'bertahap' ? 'display:block' : 'display:none' }}">
                        <label class="form-label">Satuan Bertingkat</label>

                        {{-- Satuan Tingkat 1 --}}
                        <div class="mb-2">
                            <label class="form-label">🔢 Satuan Tingkat 1</label>
                            <select name="stok_bertahap[0][satuan_id]" class="form-select mb-1 satuan-bertahap-select" data-index="0">
                                <option value="">🔽 Pilih Satuan Tingkat 1</option>
                                @foreach ($satuans as $satuan)
                                <option value="{{ $satuan->id }}"
                                    data-nama="{{ $satuan->nama_satuan }}"
                                    data-konversi="{{ $satuan->konversi_ke_satuan_utama }}"
                                    {{ old('stok_bertahap.0.satuan_id') == $satuan->id ? 'selected' : '' }}>
                                    🧱 {{ $satuan->nama_satuan }} = {{ $satuan->konversi_ke_satuan_utama }}
                                </option>
                                @endforeach
                            </select>
                            <input type="number" name="stok_bertahap[0][qty]" class="form-control" placeholder="🧮 Jumlah tingkat 1" min="0" value="{{ old('stok_bertahap.0.qty', 0) }}">
                        </div>

                        {{-- Satuan Tingkat 2 --}}
                        <div class="mb-2">
                            <label class="form-label">🔢 Satuan Tingkat 2 (opsional)</label>
                            <select name="stok_bertahap[1][satuan_id]" class="form-select mb-1 satuan-bertahap-select" data-index="1">
                                <option value="">🔽 Pilih Satuan Tingkat 2</option>
                                @foreach ($satuans as $satuan)
                                <option value="{{ $satuan->id }}"
                                    data-nama="{{ $satuan->nama_satuan }}"
                                    data-konversi="{{ $satuan->konversi_ke_satuan_utama }}"
                                    {{ old('stok_bertahap.1.satuan_id') == $satuan->id ? 'selected' : '' }}>
                                    🧱 {{ $satuan->nama_satuan }} = {{ $satuan->konversi_ke_satuan_utama }}
                                </option>
                                @endforeach
                            </select>
                            <input type="number" name="stok_bertahap[1][qty]" class="form-control" placeholder="🧮 Jumlah tingkat 2" min="0" value="{{ old('stok_bertahap.1.qty', 0) }}">
                        </div>
                    </div>

                    {{-- Lead Time --}}
                    <div class="col-md-4 mb-3">
                        <label for="lead_time" class="form-label">Lead Time (hari)</label>
                        <input type="number" name="lead_time" id="lead_time" class="form-control @error('lead_time') is-invalid @enderror" placeholder="Masukkan lead time" value="{{ old('lead_time') }}" min="0" required>
                        @error('lead_time')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Safety Stock Biasa --}}
                    <div id="safety-stock-biasa-wrapper" class="col-md-4 mb-3" style="{{ old('mode_stok', 'utama') == 'utama' ? 'display:block' : 'display:none' }}">
                        <label for="safety_stock" class="form-label">🛡️ Safety Stock</label>
                        <input type="number" name="safety_stock" id="safety_stock"
                            class="form-control @error('safety_stock') is-invalid @enderror"
                            placeholder="🧮 Masukkan jumlah safety stock"
                            value="{{ old('safety_stock') }}" min="0">
                        @error('safety_stock')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>


                    {{-- Safety Stock Bertingkat --}}
                    <div id="safety-stock-bertingkat-wrapper" class="col-md-8 mb-3" style="{{ old('mode_stok') == 'bertahap' ? 'display:block' : 'display:none' }}">
                        <label class="form-label">Safety Stock Bertingkat</label>
                        {{-- Tingkat 1 --}}
                        <div class="mb-2">
                            <select name="safety_stock_bertahap[0][satuan_id]" class="form-select mb-1 safety-stock-select" data-index="0">
                                <option value="">Pilih Satuan Tingkat 1</option>
                                @foreach ($satuans as $satuan)
                                <option value="{{ $satuan->id }}"
                                    data-nama="{{ $satuan->nama_satuan }}"
                                    data-konversi="{{ $satuan->konversi_ke_satuan_utama }}"
                                    {{ old('safety_stock_bertahap.0.satuan_id') == $satuan->id ? 'selected' : '' }}>
                                    {{ $satuan->nama_satuan }} = {{ $satuan->konversi_ke_satuan_utama }}
                                </option>
                                @endforeach
                            </select>
                            <input type="number" name="safety_stock_bertahap[0][qty]" class="form-control"
                                placeholder="Jumlah tingkat 1" min="0" value="{{ old('safety_stock_bertahap.0.qty', 0) }}">
                        </div>

                        {{-- Tingkat 2 --}}
                        <div class="mb-2">
                            <select name="safety_stock_bertahap[1][satuan_id]" class="form-select mb-1 safety-stock-select" data-index="1">
                                <option value="">Pilih Satuan Tingkat 2 (opsional)</option>
                                @foreach ($satuans as $satuan)
                                <option value="{{ $satuan->id }}"
                                    data-nama="{{ $satuan->nama_satuan }}"
                                    data-konversi="{{ $satuan->konversi_ke_satuan_utama }}"
                                    {{ old('safety_stock_bertahap.1.satuan_id') == $satuan->id ? 'selected' : '' }}>
                                    {{ $satuan->nama_satuan }} = {{ $satuan->konversi_ke_satuan_utama }}
                                </option>
                                @endforeach
                            </select>
                            <input type="number" name="safety_stock_bertahap[1][qty]" class="form-control"
                                placeholder="Jumlah tingkat 2" min="0" value="{{ old('safety_stock_bertahap.1.qty', 0) }}">
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="col-12 mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror" placeholder="Masukkan deskripsi produk" required>{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Gambar --}}
                    <div class="col-12 mb-3">
                        <label for="gambar" class="form-label">Gambar Produk</label>
                        <input type="file" name="gambar" id="gambar" class="form-control @error('gambar') is-invalid @enderror" required accept="image/*">
                        @error('gambar')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Tombol --}}
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>

                </div> {{-- end .row --}}
            </form>
        </div>
    </div>
</div>
@endsection



@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modeStokSelect = document.getElementById('mode_stok');
        const stokBiasaWrapper = document.getElementById('stok-biasa-wrapper');
        const stokBertingkatWrapper = document.getElementById('stok-bertingkat-input-wrapper');
        const stokBiasaInput = document.getElementById('stok');
        const stokFinalInput = document.getElementById('stok_final');
        const keteranganKonversi = document.getElementById('keterangan-konversi');

        const safetyStockBiasaWrapper = document.getElementById('safety-stock-biasa-wrapper');
        const safetyStockBertingkatWrapper = document.getElementById('safety-stock-bertingkat-wrapper');

        function toggleInputMode() {
            const isUtama = modeStokSelect.value === 'utama';
            stokBiasaWrapper.style.display = isUtama ? 'block' : 'none';
            stokBertingkatWrapper.style.display = isUtama ? 'none' : 'block';
            safetyStockBiasaWrapper.style.display = isUtama ? 'block' : 'none';
            safetyStockBertingkatWrapper.style.display = isUtama ? 'none' : 'block';

            updateStokFinal();
            if (!isUtama) tampilkanKeteranganKonversi();
        }

        function updateStokFinal() {
            let totalStok = 0;

            if (modeStokSelect.value === 'utama') {
                totalStok = parseFloat(stokBiasaInput.value) || 0;
            } else {
                document.querySelectorAll('.satuan-bertahap-select').forEach(select => {
                    const index = select.dataset.index;
                    const inputQty = document.querySelector(`input[name="stok_bertahap[${index}][qty]"]`);
                    const konversi = parseFloat(select.selectedOptions[0]?.dataset?.konversi || 1);
                    const qty = parseFloat(inputQty?.value || 0);
                    totalStok += qty * konversi;
                });
            }

            if (stokFinalInput) stokFinalInput.value = totalStok;
        }

        function tampilkanKeteranganKonversi() {
            let teks = '';
            document.querySelectorAll('.satuan-bertahap-select').forEach(select => {
                const option = select.options[select.selectedIndex];
                const nama = option?.dataset?.nama;
                const konversi = option?.dataset?.konversi;

                if (nama && konversi) {
                    teks += `${nama} = ${konversi}<br>`;
                }
            });
            if (keteranganKonversi) keteranganKonversi.innerHTML = teks;
        }

        // Event listener perubahan mode
        modeStokSelect.addEventListener('change', toggleInputMode);
        stokBiasaInput.addEventListener('input', updateStokFinal);

        document.querySelectorAll('.stok-bertahap-input').forEach(input => {
            input.addEventListener('input', updateStokFinal);
        });

        document.querySelectorAll('.satuan-bertahap-select').forEach(select => {
            select.addEventListener('change', () => {
                tampilkanKeteranganKonversi();
                updateStokFinal();
            });
        });

        // Inisialisasi awal saat halaman load
        toggleInputMode();
    });
</script>



@endsection