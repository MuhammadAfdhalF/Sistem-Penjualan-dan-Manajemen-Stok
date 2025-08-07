@extends('layouts.mantis')

@section('title', 'Halaman Tambah Produk Terpadu')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('produk.index') }}" style="opacity: 0.5;">Produk</a></li>
<li class="breadcrumb-item"><strong><a href="{{ route('produk.create') }}">Tambah Produk Terpadu</a></strong></li>
@endsection

@section('content')
<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Tahap 1/2: Tambah Data Produk</h4>
            <a href="{{ route('produk.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        <div class="card-body">
            <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- FORM PRODUK DASAR --}}
                <h5 class="mb-3">Informasi Dasar Produk</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk" class="form-control" placeholder="Contoh: Sampoerna Mild" value="{{ old('nama_produk') }}" required autofocus>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <div id="kategori-input-group">
                            <div class="input-group">
                                <select name="kategori" id="kategori_select" class="form-control form-select" required>
                                    <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>Pilih kategori</option>
                                    <option value="Kebutuhan Rumah Tangga" {{ old('kategori') == 'Kebutuhan Rumah Tangga' ? 'selected' : '' }}>Kebutuhan Rumah Tangga</option>
                                    <option value="Bahan Makanan Pokok" {{ old('kategori') == 'Bahan Makanan Pokok' ? 'selected' : '' }}>Bahan Makanan Pokok</option>
                                    <option value="Makanan dan Minuman Kemasan" {{ old('kategori') == 'Makanan dan Minuman Kemasan' ? 'selected' : '' }}>Makanan dan Minuman Kemasan</option>
                                    <option value="Rokok dan Produk Tembakau" {{ old('kategori') == 'Rokok dan Produk Tembakau' ? 'selected' : '' }}>Rokok dan Produk Tembakau</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-primary" id="btn-tambah-kategori" title="Tambah Kategori Baru">+</button>
                            </div>
                            <div class="input-group mt-2" id="kategori-input-baru" style="display: none;">
                                <input type="text" name="kategori_baru" id="kategori_input_text" class="form-control" placeholder="Masukkan kategori baru">
                                <button type="button" class="btn btn-sm btn-secondary" id="btn-batal-kategori">Batal</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="lead_time" class="form-label">Lead Time (hari)</label>
                        <input type="number" name="lead_time" id="lead_time" class="form-control" placeholder="Contoh: 7" value="{{ old('lead_time') }}" min="0" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <small class="form-text text-muted">Durasi waktu dari pemesanan hingga barang tiba.</small>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" class="form-control" placeholder="Masukkan deskripsi lengkap produk" required>{{ old('deskripsi') }}</textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="gambar" class="form-label">Gambar Produk</label>
                        <input type="file" name="gambar" id="gambar" class="form-control" required accept="image/*">
                        <div class="mt-2">
                            <img id="preview-gambar" src="#" alt="Preview Gambar" style="display:none;max-height:150px;">
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Satuan & Harga Produk</h5>
                <div id="container-satuan">
                    {{-- Blok satuan dan harga akan ditambahkan di sini --}}
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-satuan">
                    <i class="ti ti-plus"></i> Tambah Satuan
                </button>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success">Simpan dan Lanjutkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const containerSatuan = document.getElementById('container-satuan');
        const btnTambahSatuan = document.getElementById('btn-tambah-satuan');
        let counter = 0;

        function formatRupiah(angka) {
            let number_string = angka.toString().replace(/[^,\d]/g, '');
            let split = number_string.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        function addSatuanBlock() {
            const block = document.createElement('div');
            block.classList.add('row', 'g-3', 'mb-4', 'p-3', 'border', 'rounded', 'bg-light', 'satuan-block');
            block.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6>Satuan #${counter + 1}</h6>
                    <button type="button" class="btn-close btn-hapus-satuan"></button>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Satuan</label>
                    <input type="text" name="satuans[${counter}][nama_satuan]" class="form-control" placeholder="Contoh: dus, pack" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Level Satuan</label>
                    <input type="number" name="satuans[${counter}][level]" class="form-control" placeholder="Contoh: 2" min="1" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <small class="form-text text-muted">Angka lebih besar untuk satuan yang lebih besar.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Konversi ke Satuan Utama</label>
                    <input type="number" step="0.0001" name="satuans[${counter}][konversi]" class="form-control konversi-input" placeholder="Contoh: 10 (jika 1 dus = 10 pcs)" required oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga untuk Toko Kecil</label>
                    <input type="text" class="form-control harga-input" placeholder="Harga Toko Kecil">
                    <input type="hidden" name="satuans[${counter}][harga_toko_kecil]" class="harga-hidden">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga untuk Individu</label>
                    <input type="text" class="form-control harga-input" placeholder="Harga Individu">
                    <input type="hidden" name="satuans[${counter}][harga_individu]" class="harga-hidden">
                </div>
            `;
            containerSatuan.appendChild(block);

            block.querySelectorAll('.harga-input').forEach(input => {
                input.addEventListener('input', function() {
                    let rawValue = this.value.replace(/\./g, '');
                    this.nextElementSibling.value = rawValue;
                    this.value = formatRupiah(rawValue);
                });
            });

            counter++;
        }

        btnTambahSatuan.addEventListener('click', addSatuanBlock);

        containerSatuan.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-hapus-satuan')) {
                e.target.closest('.satuan-block').remove();
            }
        });

        const gambarInput = document.getElementById('gambar');
        const gambarPreview = document.getElementById('preview-gambar');
        gambarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    gambarPreview.src = event.target.result;
                    gambarPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                gambarPreview.src = '#';
                gambarPreview.style.display = 'none';
            }
        });

        const selectKategori = document.getElementById('kategori_select');
        const inputKategoriText = document.getElementById('kategori_input_text');
        const inputKategoriBaruContainer = document.getElementById('kategori-input-baru');
        const btnTambahKategori = document.getElementById('btn-tambah-kategori');
        const btnBatalKategori = document.getElementById('btn-batal-kategori');

        let kategoriSelectedValue = selectKategori.value;
        let isManualInput = false;

        btnTambahKategori.addEventListener('click', function() {
            selectKategori.style.display = 'none';
            btnTambahKategori.style.display = 'none';
            inputKategoriBaruContainer.style.display = 'flex';
            if (kategoriSelectedValue) {
                inputKategoriText.value = kategoriSelectedValue;
            }
            selectKategori.disabled = true;
            inputKategoriText.name = 'kategori';
            selectKategori.name = 'kategori_old';
            isManualInput = true;
        });

        btnBatalKategori.addEventListener('click', function() {
            selectKategori.style.display = 'block';
            btnTambahKategori.style.display = 'inline-block';
            inputKategoriBaruContainer.style.display = 'none';
            selectKategori.disabled = false;
            selectKategori.name = 'kategori';
            inputKategoriText.name = 'kategori_baru';
            inputKategoriText.value = '';
            isManualInput = false;
        });

        selectKategori.addEventListener('change', function() {
            kategoriSelectedValue = selectKategori.value;
        });

        const oldKategoriBaru = '{{ old('
        kategori_baru ') }}';
        if (oldKategoriBaru) {
            btnTambahKategori.click();
        }

        addSatuanBlock();
    });
</script>
@endpush