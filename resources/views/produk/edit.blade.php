@extends('layouts.mantis')

@section('title', 'Halaman Edit Produk')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('produk.index') }}" style="opacity: 0.5;">Produk</a></li>
<li class="breadcrumb-item"><strong>Edit Data Produk</strong></li>
@endsection

@section('content')
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

                {{-- FORM PRODUK DASAR --}}
                <h5 class="mb-3">Informasi Dasar Produk</h5>
                <div class="row">
                    {{-- Nama Produk --}}
                    <div class="col-md-6 mb-3">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" id="nama_produk" class="form-control"
                            placeholder="Contoh: Sampoerna Mild"
                            value="{{ old('nama_produk', $produk->nama_produk) }}" required autofocus>
                    </div>

                    {{-- Kategori --}}
                    <div class="col-md-6 mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <div id="kategori-input-group">
                            <div class="input-group">
                                <select name="kategori" id="kategori_select" class="form-control form-select" required>
                                    <option value="" disabled>Pilih kategori</option>
                                    <option value="Kebutuhan Rumah Tangga" {{ old('kategori', $produk->kategori) == 'Kebutuhan Rumah Tangga' ? 'selected' : '' }}>Kebutuhan Rumah Tangga</option>
                                    <option value="Bahan Makanan Pokok" {{ old('kategori', $produk->kategori) == 'Bahan Makanan Pokok' ? 'selected' : '' }}>Bahan Makanan Pokok</option>
                                    <option value="Makanan dan Minuman Kemasan" {{ old('kategori', $produk->kategori) == 'Makanan dan Minuman Kemasan' ? 'selected' : '' }}>Makanan dan Minuman Kemasan</option>
                                    <option value="Rokok dan Produk Tembakau" {{ old('kategori', $produk->kategori) == 'Rokok dan Produk Tembakau' ? 'selected' : '' }}>Rokok dan Produk Tembakau</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-primary" id="btn-tambah-kategori" title="Tambah Kategori Baru">+</button>
                            </div>
                            <div class="input-group mt-2" id="kategori-input-baru" style="display: none;">
                                <input type="text" name="kategori_baru" id="kategori_input_text" class="form-control" placeholder="Masukkan kategori baru">
                                <button type="button" class="btn btn-sm btn-secondary" id="btn-batal-kategori">Batal</button>
                            </div>
                        </div>
                    </div>

                    {{-- Lead Time --}}
                    <div class="col-md-4 mb-3">
                        <label for="lead_time" class="form-label">Lead Time (hari)</label>
                        <input type="number" name="lead_time" id="lead_time" class="form-control"
                            placeholder="Contoh: 7" value="{{ old('lead_time', $produk->lead_time) }}" min="0" required
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <small class="form-text text-muted">Durasi waktu dari pemesanan hingga barang tiba.</small>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="col-12 mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" class="form-control"
                            placeholder="Masukkan deskripsi lengkap produk"
                            required>{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                    </div>

                    {{-- Gambar --}}
                    <div class="col-12 mb-3">
                        <label for="gambar" class="form-label">Gambar Produk</label>
                        @if ($produk->gambar)
                        <br>
                        <img src="{{ asset('storage/gambar_produk/' . $produk->gambar) }}" width="120" class="mb-2 rounded"
                            alt="Gambar Produk" id="current_gambar">
                        @endif
                        <input type="file" name="gambar" id="gambar" class="form-control" accept="image/*">
                        <div class="mt-2">
                            <img id="preview-gambar" src="#" alt="Preview Gambar" style="display:none;max-height:150px;">
                        </div>
                    </div>

                    {{-- Hidden inputs yang tidak relevan --}}
                    <input type="hidden" name="daily_usage" value="{{ old('daily_usage', $produk->daily_usage) }}">
                    <input type="hidden" name="safety_stock" value="{{ old('safety_stock', $produk->safety_stock) }}">
                </div>

                <hr class="my-4">

                {{-- FORM SATUAN DAN HARGA DINAMIS --}}
                <h5 class="mb-3">Satuan & Harga Produk</h5>
                <div id="container-satuan">
                    {{-- Blok satuan dan harga akan dimuat di sini oleh JavaScript --}}
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="btn-tambah-satuan">
                    <i class="ti ti-plus"></i> Tambah Satuan
                </button>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const produkData = @json($produk);
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

        function addSatuanBlock(satuan = null) {
            const block = document.createElement('div');
            block.classList.add('row', 'g-3', 'mb-4', 'p-3', 'border', 'rounded', 'bg-light', 'satuan-block');
            block.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6>Satuan #${counter + 1}</h6>
                    <button type="button" class="btn-close btn-hapus-satuan"></button>
                </div>
                <input type="hidden" name="satuans[${counter}][id]" value="${satuan ? satuan.id : ''}">
                <div class="col-md-4">
                    <label class="form-label">Nama Satuan</label>
                    <input type="text" name="satuans[${counter}][nama_satuan]" class="form-control" placeholder="Contoh: dus, pack" value="${satuan ? satuan.nama_satuan : ''}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Level Satuan</label>
                    <input type="number" name="satuans[${counter}][level]" class="form-control" placeholder="Contoh: 2" min="1" value="${satuan ? satuan.level : ''}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <small class="form-text text-muted">Angka lebih besar untuk satuan yang lebih besar.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Konversi ke Satuan Utama</label>
                    <input type="number" step="0.0001" name="satuans[${counter}][konversi]" class="form-control konversi-input" placeholder="Contoh: 10" value="${satuan ? satuan.konversi_ke_satuan_utama : ''}" required oninput="this.value = this.value.replace(/[^0-9.]/g, '')">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga untuk Toko Kecil</label>
                    <input type="text" class="form-control harga-input" placeholder="Harga Toko Kecil" value="${satuan && satuan.harga_produk ? formatRupiah(satuan.harga_produk.filter(h => h.jenis_pelanggan === 'Toko Kecil')[0]?.harga || 0) : ''}">
                    <input type="hidden" name="satuans[${counter}][harga_toko_kecil]" class="harga-hidden" value="${satuan && satuan.harga_produk ? (satuan.harga_produk.filter(h => h.jenis_pelanggan === 'Toko Kecil')[0]?.harga || '') : ''}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Harga untuk Individu</label>
                    <input type="text" class="form-control harga-input" placeholder="Harga Individu" value="${satuan && satuan.harga_produk ? formatRupiah(satuan.harga_produk.filter(h => h.jenis_pelanggan === 'Individu')[0]?.harga || 0) : ''}">
                    <input type="hidden" name="satuans[${counter}][harga_individu]" class="harga-hidden" value="${satuan && satuan.harga_produk ? (satuan.harga_produk.filter(h => h.jenis_pelanggan === 'Individu')[0]?.harga || '') : ''}">
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

        // Muat data satuan yang sudah ada
        if (produkData.satuans && produkData.satuans.length > 0) {
            produkData.satuans.forEach(satuan => {
                addSatuanBlock(satuan);
            });
        }

        btnTambahSatuan.addEventListener('click', () => addSatuanBlock());

        containerSatuan.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-hapus-satuan')) {
                e.target.closest('.satuan-block').remove();
            }
        });

        // Event listener untuk preview gambar
        const gambarInput = document.getElementById('gambar');
        const gambarPreview = document.getElementById('preview-gambar');
        gambarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    gambarPreview.src = event.target.result;
                    gambarPreview.style.display = 'block';
                    document.getElementById('current_gambar').style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                gambarPreview.src = '#';
                gambarPreview.style.display = 'none';
                document.getElementById('current_gambar').style.display = 'block';
            }
        });

        // Logika untuk dropdown kategori
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
    });
</script>
@endpush