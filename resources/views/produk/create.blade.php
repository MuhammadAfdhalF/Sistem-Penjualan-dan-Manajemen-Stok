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

                    {{-- Pilihan input stok: satuan utama atau bertingkat --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Input Stok</label>
                        <select id="stok_type" name="stok_type" class="form-select mb-2">
                            <option value="utama" {{ old('stok_type', 'utama') == 'utama' ? 'selected' : '' }}>
                                Stok Satuan Utama ({{ old('satuan_utama', $produk->satuan_utama ?? 'pcs') }})
                            </option>
                            <option value="bertingkat" {{ old('stok_type') == 'bertingkat' ? 'selected' : '' }}>
                                Stok Satuan Bertingkat
                            </option>
                        </select>

                        {{-- Input stok satuan utama --}}
                        <div id="stok-utama-input-wrapper" style="{{ old('stok_type', 'utama') == 'utama' ? 'display:block' : 'display:none' }}">
                            <input type="number" name="stok_utama" id="stok_utama" class="form-control @error('stok_utama') is-invalid @enderror"
                                placeholder="Masukkan stok produk" value="{{ old('stok_utama', $produk->stok ?? 0) }}" min="0">
                            @error('stok_utama')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Input stok satuan bertingkat --}}
                        <div id="stok-bertingkat-input-wrapper" style="{{ old('stok_type') == 'bertingkat' ? 'display:block' : 'display:none' }}">
                            <select name="satuan_bertingkat" id="satuan_bertingkat" class="form-select mb-2 @error('satuan_bertingkat') is-invalid @enderror">
                                <option value="" disabled {{ old('satuan_bertingkat') ? '' : 'selected' }}>Pilih satuan bertingkat</option>
                                @foreach($satuanBertingkat as $satuan)
                                <option value="{{ $satuan->konversi_ke_satuan_utama }}"
                                    {{ old('satuan_bertingkat') == $satuan->konversi_ke_satuan_utama ? 'selected' : '' }}>
                                    {{ $satuan->nama_satuan }} (Konversi ke {{ old('satuan_utama', $produk->satuan_utama ?? 'pcs') }}: {{ $satuan->konversi_ke_satuan_utama }})
                                </option>
                                @endforeach
                            </select>
                            @error('satuan_bertingkat')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror

                            <input type="number" name="stok_bertingkat_qty" id="stok_bertingkat_qty"
                                class="form-control @error('stok_bertingkat_qty') is-invalid @enderror"
                                placeholder="Masukkan jumlah satuan bertingkat"
                                value="{{ old('stok_bertingkat_qty', 0) }}" min="0">
                            @error('stok_bertingkat_qty')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Hidden input stok final, opsional jika mau update dengan JS --}}
                        <input type="hidden" name="stok" id="stok_final" value="{{ old('stok', $produk->stok ?? 0) }}">
                    </div>

                    <!-- Satuan Utama-->

                    <div class="col-md-6 mb-3">
                        <label for="satuan_utama" class="form-label">Satuan</label>
                        <select name="satuan_utama" id="satuan_utama" class="form-control @error('satuan_utama') is-invalid @enderror" required>
                            <option value="" disabled {{ old('satuan_utama') ? '' : 'selected' }}>Pilih satuan</option>
                            <option value="bks" {{ old('satuan_utama') == 'bks' ? 'selected' : '' }}>bks</option>
                            <option value="pcs" {{ old('satuan_utama') == 'pcs' ? 'selected' : '' }}>pcs</option>
                            <option value="kg" {{ old('satuan_utama') == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="liter" {{ old('satuan_utama') == 'liter' ? 'selected' : '' }}>liter</option>
                            <option value="box" {{ old('satuan_utama') == 'box' ? 'selected' : '' }}>box</option>
                        </select>
                        @error('satuan_utama')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Input Lead Time -->
                    <div class="col-md-4 mb-3">
                        <label for="lead_time" class="form-label">Lead Time (hari)</label>
                        <input type="number" name="lead_time" id="lead_time" class="form-control @error('lead_time') is-invalid @enderror" placeholder="Masukkan lead time" value="{{ old('lead_time') }}" min="0" required>
                        @error('lead_time')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>


                    <!-- Input Safety Stock -->
                    <div class="col-md-4 mb-3">
                        <label for="safety_stock" class="form-label">Safety Stock</label>
                        <input type="number" name="safety_stock" id="safety_stock" class="form-control @error('safety_stock') is-invalid @enderror" placeholder="Masukkan safety stock" value="{{ old('safety_stock') }}" min="0" required>
                        @error('safety_stock')
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

@section('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stokType = document.getElementById('stok_type');
        const stokUtamaWrapper = document.getElementById('stok-utama-input-wrapper');
        const stokBertingkatWrapper = document.getElementById('stok-bertingkat-input-wrapper');
        const stokUtamaInput = document.getElementById('stok_utama');
        const satuanBertingkatSelect = document.getElementById('satuan_bertingkat');
        const stokBertingkatQtyInput = document.getElementById('stok_bertingkat_qty');
        const stokFinalInput = document.getElementById('stok_final');

        function updateStokFinal() {
            if (stokType.value === 'utama') {
                const val = parseInt(stokUtamaInput.value) || 0;
                stokFinalInput.value = val;
            } else {
                const konversi = parseInt(satuanBertingkatSelect.value) || 0;
                const qty = parseInt(stokBertingkatQtyInput.value) || 0;
                stokFinalInput.value = konversi * qty;
            }
        }

        stokType.addEventListener('change', () => {
            if (stokType.value === 'utama') {
                stokUtamaWrapper.style.display = 'block';
                stokBertingkatWrapper.style.display = 'none';
            } else {
                stokUtamaWrapper.style.display = 'none';
                stokBertingkatWrapper.style.display = 'block';
            }
            updateStokFinal();
        });

        stokUtamaInput.addEventListener('input', updateStokFinal);
        satuanBertingkatSelect.addEventListener('change', updateStokFinal);
        stokBertingkatQtyInput.addEventListener('input', updateStokFinal);

        // Initialize stok final on page load
        updateStokFinal();
    });
</script>
@endsection