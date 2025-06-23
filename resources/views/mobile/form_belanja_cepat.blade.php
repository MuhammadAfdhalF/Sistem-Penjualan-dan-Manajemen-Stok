@extends('layouts.template_mobile')
@section('title', 'Form Belanja Cepat - KZ Family')

@push('head')
<style>
    /* STYLE ANDA TETAP SAMA, TIDAK DIUBAH */
    body {
        background-color: #f8f9fa;
    }

    .product-card {
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.21);
        margin-bottom: 1rem;
        transition: box-shadow 0.2s ease-in-out;
    }

    /* Optional hover effect */
    .product-card:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .product-card.selected {
        box-shadow: 0 4px 16px rgba(19, 82, 145, 0.25);
        border: 1px solid #135291;
    }

    .product-image {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
    }

    .custom-check {
        width: 20px;
        height: 20px;
        appearance: none;
        -webkit-appearance: none;
        background-color: #fff;
        border: 2px solid #135291;
        border-radius: 6px;
        cursor: pointer;
        position: relative;
        display: inline-block;
        transition: all 0.2s ease-in-out;
    }

    .custom-check:checked {
        background-color: #135291;
    }

    .custom-check:checked::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 6px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    @media (max-width: 768px) {
        .filter-wrapper {
            margin-left: 0.75rem;
            margin-right: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .filter-wrapper {
            /* Perintah ini tetap ada untuk memaksa filter menjadi satu baris */
            flex-wrap: nowrap !important;

        }

        /* Atur lebar untuk input pencarian (lebih besar) */
        .filter-wrapper .filter-input {
            width: 60%;
            /* Input pencarian mengambil 60% dari lebar */
            flex-grow: 0;
            /* Pastikan tidak tumbuh otomatis lagi, agar ukurannya pas 60% */
        }

        /* Atur lebar untuk grup kategori (lebih kecil) */
        .filter-wrapper .filter-select-group {
            width: 38%;
            /* Grup kategori mengambil sisa ruang (kurang sedikit untuk jarak/gap) */
        }

        main.main-content {
            margin-bottom: 0px;
            padding-bottom: 0px;
        }

    }

    @media (max-width: 991.98px) {
        .col-lg-8 {
            margin-bottom: 5rem !important;
        }
    }


    @media (max-width: 1280px) and (orientation: landscape) {
        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
    }

    @media (min-width: 600px) and (max-width: 1024px) {
        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
    }

    /* Hide footer info on mobile and tablet */
    @media (max-width: 1024px) {
        .footer-info {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0 px-lg-3 py-3 desktop-container " style="max-width: 1280px;">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 ms-3 mt-3 d-block d-lg-none">
        <div>
            <h6 class="fw-bold mb-1 text-body">Toko KZ Family</h6>
            <small class="text-muted">Form Belanja Cepat</small>
        </div>
        <div class="me-3">
            <a href="{{ route('mobile.keranjang.index') }}" class="btn bg-white shadow rounded-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-cart text-dark" style="font-size: 1.2rem;"></i>
            </a>
        </div>
    </div>

    <!-- Banner Welcome -->
    <div class="w-100 d-flex justify-content-center">
        <div class="alert alert-light text-center mx-auto" style="max-width: 700px; width: 95%; border: 1px solid rgba(0, 0, 0, 0.3); border-radius: 8px;">
            <div class="d-flex flex-column align-items-center">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-stars fs-5 me-2" style="color: #135291;"></i>
                    <strong class="fw-semibold text-dark">Selamat Datang di Cara Belanja Terbaik !!!</strong>
                </div>
                <p class="mb-0 small text-body-secondary">
                    Pilih semua produk kebutuhanmu secara grosir dan eceran, kemudian langsung checkout. Gak perlu lagi bolak-balik masukin ke keranjang!
                </p>
            </div>
        </div>
    </div>

    <!-- ===== FORM UNTUK FILTER ===== -->
    <form action="{{ route('mobile.form_belanja_cepat.index') }}" method="GET" id="filter-form">
        <div class="d-flex flex-wrap justify-content-between gap-1 mb-4 filter-wrapper">
            <div class="d-flex align-items-center shadow-sm px-3 flex-grow-1 filter-input" style="background-color: #fff; height: 44px; border: 1px solid rgba(0, 0, 0, 0.38); border-radius: 8px;">
                <span class="me-2" style="font-size: 1rem;">🔍</span>
                <input type="text" name="search" class="form-control border-0 shadow-none p-0" placeholder="Cari Produk diinginkan...." value="{{ $searchQuery ?? '' }}" style="font-size: clamp(0.75rem, 1.5vw, 1rem); background-color: transparent;">
            </div>

            {{-- Grup untuk Select dan Tombol Reset --}}
            <div class="d-flex gap-2 filter-select-group">
                <div class="flex-grow-1">
                    <select name="kategori" class="form-select shadow-sm w-100" style="height: 44px; font-size: clamp(0.75rem, 1.5vw, 0.95rem); border: 1px solid rgba(0, 0, 0, 0.38); border-radius: 8px;" onchange="this.form.submit()">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($listKategori as $k)
                        <option value="{{ $k }}" @if(isset($filterKategori) && $filterKategori==$k) selected @endif>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Reset, muncul jika ada filter aktif --}}
                @if(!empty($searchQuery) || !empty($filterKategori))
                <a href="{{ route('mobile.form_belanja_cepat.index') }}" class="btn btn-light d-flex align-items-center justify-content-center" title="Reset Filter" style="height: 44px; border: 1px solid rgba(0,0,0,0.38); width: 44px; border-radius: 8px;">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>

        </div>
    </form>
    <!-- ===== AKHIR FORM FILTER ===== -->


    <!-- ===== FORM UNTUK CHECKOUT ===== -->
    <form action="{{ route('mobile.form_belanja_cepat.konfirmasi') }}" method="POST" id="form-belanja-cepat">
        @csrf

        <!-- Layout Produk & Checkout -->
        <div class="row">
            <!-- Produk (kiri) -->
            <div class="col-lg-8">
                @forelse($produk as $p)
                <div class="card product-card p-3 mb-2 shadow-sm" data-produk-id="{{ $p->id }}">
                    <div class="d-flex align-items-center gap-3"> {{-- ubah align-items-start -> center --}}
                        <div class="d-flex align-items-center" style="height: 100%;">
                            <input type="checkbox" class="custom-check product-selector">
                        </div>
                        <img src="{{ $p->gambar ? asset('storage/gambar_produk/' . $p->gambar) : asset('assets/img/no-image.jpg') }}" class="product-image" alt="{{ $p->nama_produk }}">

                        <div class="flex-grow-1">
                            <h6 class="fw-semibold text-body mb- text-wrap">{{ $p->nama_produk }}</h6>

                            <div class="text-muted small mb-1">
                                @php
                                $hargaList = $p->satuans->map(function($satuan) use ($p) {
                                $hargaObj = $p->hargaProduks->firstWhere('satuan_id', $satuan->id);
                                if ($hargaObj) {
                                return 'Rp ' . number_format($hargaObj->harga, 0, ',', '.') . '/' . $satuan->nama_satuan;
                                }
                                return null;
                                })->filter()->toArray();
                                @endphp
                                {!! implode('<br>', $hargaList) !!}
                            </div>

                            <div class="text-muted small mb-2">Tersedia : {{ $p->stok_bertingkat }}</div>

                            <div class="jumlah-satuan-wrapper text-end">
                                <div class="row gx-2 align-items-center satuan-group mb-2 justify-content-end">
                                    <div class="col-auto">
                                        <div class="input-group input-group-sm" style="border: none;">
                                            <button class="btn btn-sm bg-light border-0 btn-minus" type="button" style="font-size: 0.75rem;">−</button>
                                            <input type="text" class="form-control text-center jumlah-input bg-light border-0" placeholder="0" value="" style="width: 45px; font-size: 0.75rem;">
                                            <button class="btn btn-sm bg-light border-0 btn-plus" type="button" style="font-size: 0.75rem;">+</button>
                                        </div>
                                    </div>
                                    <div class="col-auto" style="width: 80px;">
                                        <select class="form-select form-select-sm border-0 bg-light satuan-select" style="font-size: 0.75rem;">
                                            @foreach($p->satuans as $satuan)
                                            @php
                                            $hargaSatuan = $p->hargaProduks->firstWhere('satuan_id', $satuan->id);
                                            @endphp
                                            <option value="{{ $satuan->id }}" data-harga="{{ $hargaSatuan->harga ?? 0 }}">{{ $satuan->nama_satuan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-auto" style="width: 40px;">
                                        <button type="button" class="btn btn-sm btn-light text-success tambah-jumlah w-100" style="font-size: 0.75rem;"><i class="bi bi-plus-lg"></i></button>
                                    </div>
                                    <div class="col-auto" style="width: 40px;">
                                        <button type="button" class="btn btn-sm btn-light text-danger hapus-jumlah w-100 d-none" style="font-size: 0.75rem;"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @empty
                <div class="text-center py-5">
                    <p class="text-muted">Produk tidak ditemukan.</p>
                </div>
                @endforelse
            </div>

            <!-- Checkout (kanan desktop saja) -->
            <div class="col-lg-4 d-none d-lg-block mt-3 mt-lg-0">
                <div class="bg-white p-4" style="box-shadow: 0 12px 42px rgba(0, 0, 0, 0.08), 0 3px 10px rgba(0, 0, 0, 0.04); border-radius: 18px;">
                    <div class="fw-semibold text-dark mb-1 fs-6">Total Produk : <span class="text-dark fw-bold" id="total-produk-desktop">0 produk</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                        <span class="text-secondary fs-6">Total</span>
                        <span class="fw-bold fs-5" style="color: #135291;" id="total-harga-desktop">Rp 0</span>
                    </div>
                    <button type="submit" class="btn w-100 fw-bold checkout-btn" style="border-radius: 12px; font-size: 1rem; padding: 12px 0; background-color: #135291; color: white;">
                        CHECKOUT !!!
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden inputs untuk data yang akan disubmit -->
        <div id="form-data-container"></div>
    </form>
    <!-- ===== AKHIR FORM CHECKOUT ===== -->


    <!-- Footer Mobile Checkout -->
    <div class="fixed-bottom bg-white border-top shadow-sm p-2 d-lg-none" style="bottom: 65px; z-index: 102;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-start">
                <small class="text-muted d-block">Total Produk: <strong class="text-dark" id="total-produk-mobile">0 produk</strong></small>
                <small class="text-muted d-block">Total: <strong class="text-dark" id="total-harga-mobile">Rp 0</strong></small>
            </div>
            <button type="submit" form="form-belanja-cepat" class="btn btn-primary fw-bold px-4 checkout-btn" style="background-color: #135291; color: white; border-radius: 10px; margin-right: 10px;">
                Checkout !!!
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===============================================
        // ============ LOGIKA UNTUK CHECKOUT ============
        // ===============================================
        const mainForm = document.getElementById('form-belanja-cepat');

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(angka);
        }

        function calculateTotal() {
            let totalProduk = 0;
            let totalHarga = 0;

            document.querySelectorAll('.product-card.selected').forEach(card => {
                let hasValue = false;
                card.querySelectorAll('.satuan-group').forEach(group => {
                    const jumlahInput = group.querySelector('.jumlah-input');
                    const satuanSelect = group.querySelector('.satuan-select');
                    const jumlah = parseInt(jumlahInput.value) || 0;

                    if (jumlah > 0) {
                        hasValue = true;
                        const selectedOption = satuanSelect.options[satuanSelect.selectedIndex];
                        const harga = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
                        totalHarga += jumlah * harga;
                    }
                });
                if (hasValue) {
                    totalProduk++;
                }
            });

            document.getElementById('total-produk-desktop').textContent = `${totalProduk} produk`;
            document.getElementById('total-harga-desktop').textContent = formatRupiah(totalHarga);
            document.getElementById('total-produk-mobile').textContent = `${totalProduk} produk`;
            document.getElementById('total-harga-mobile').textContent = formatRupiah(totalHarga);
        }

        function handleInteraction(event) {
            const card = event.target.closest('.product-card');
            if (!card) return;

            const selector = card.querySelector('.product-selector');
            let isAnyInputFilled = false;
            card.querySelectorAll('.jumlah-input').forEach(input => {
                if ((parseInt(input.value) || 0) > 0) {
                    isAnyInputFilled = true;
                }
            });

            if (isAnyInputFilled) {
                selector.checked = true;
                card.classList.add('selected');
            } else {
                selector.checked = false;
                card.classList.remove('selected');
            }
            calculateTotal();
        }

        function bindSatuanGroupEvents(group) {
            group.addEventListener('input', handleInteraction);
            group.addEventListener('click', function(e) {
                const target = e.target;
                const input = target.closest('.input-group')?.querySelector('.jumlah-input');

                if (!input) return;
                let val = parseInt(input.value) || 0;
                if (target.classList.contains('btn-minus') && val > 0) {
                    input.value = val - 1;
                }
                if (target.classList.contains('btn-plus')) {
                    input.value = val + 1;
                }
                handleInteraction(e);
            });

            const tambahBtn = group.querySelector('.tambah-jumlah');
            const hapusBtn = group.querySelector('.hapus-jumlah');

            tambahBtn.addEventListener('click', function() {
                const clone = group.cloneNode(true);
                const wrapper = group.closest('.jumlah-satuan-wrapper');

                clone.querySelector('.jumlah-input').value = '';
                clone.querySelector('.hapus-jumlah').classList.remove('d-none');

                wrapper.appendChild(clone);
                bindSatuanGroupEvents(clone);
            });

            hapusBtn.addEventListener('click', function() {
                const wrapper = group.closest('.jumlah-satuan-wrapper');
                if (wrapper.querySelectorAll('.satuan-group').length > 1) {
                    group.remove();
                    handleInteraction({
                        target: wrapper
                    });
                }
            });
        }

        document.querySelectorAll('.satuan-group').forEach(bindSatuanGroupEvents);

        document.querySelectorAll('.product-selector').forEach(selector => {
            selector.addEventListener('change', function() {
                const card = this.closest('.product-card');
                if (this.checked) {
                    card.classList.add('selected');
                    const firstInput = card.querySelector('.jumlah-input');
                    if (!(parseInt(firstInput.value) > 0)) {
                        firstInput.value = 1;
                    }
                } else {
                    card.classList.remove('selected');
                    card.querySelectorAll('.jumlah-input').forEach(input => input.value = '');
                }
                calculateTotal();
            });
        });

        mainForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const dataContainer = document.getElementById('form-data-container');
            dataContainer.innerHTML = '';

            let produkIndex = 0;
            document.querySelectorAll('.product-card.selected').forEach(card => {
                const produkId = card.getAttribute('data-produk-id');
                const jumlahJson = {};
                let hasValidQuantity = false;

                card.querySelectorAll('.satuan-group').forEach(group => {
                    const jumlah = parseInt(group.querySelector('.jumlah-input').value) || 0;
                    const satuanId = group.querySelector('.satuan-select').value;
                    if (jumlah > 0) {
                        jumlahJson[satuanId] = (jumlahJson[satuanId] || 0) + jumlah;
                        hasValidQuantity = true;
                    }
                });

                if (hasValidQuantity) {
                    const produkIdInput = document.createElement('input');
                    produkIdInput.type = 'hidden';
                    produkIdInput.name = `produk_data[${produkIndex}][produk_id]`;
                    produkIdInput.value = produkId;
                    dataContainer.appendChild(produkIdInput);

                    for (const [satuanId, qty] of Object.entries(jumlahJson)) {
                        const jumlahInput = document.createElement('input');
                        jumlahInput.type = 'hidden';
                        jumlahInput.name = `produk_data[${produkIndex}][jumlah_json][${satuanId}]`;
                        jumlahInput.value = qty;
                        dataContainer.appendChild(jumlahInput);
                    }
                    produkIndex++;
                }
            });

            if (produkIndex === 0) {
                alert('Silakan pilih dan isi jumlah produk terlebih dahulu.');
                return;
            }

            this.submit();
        });

        // ===============================================
        // ===== LOGIKA UNTUK FILTER DAN LIVE SEARCH =====
        // ===============================================
        const searchInput = document.querySelector('#filter-form input[name="search"]');
        const filterForm = document.getElementById('filter-form');
        let debounceTimer;

        // Hapus logic submit otomatis jika search input dikosongkan secara manual
        // agar tidak konflik dengan tombol reset.
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                // Hanya submit jika input tidak kosong.
                if (searchInput.value.trim() !== '') {
                    filterForm.submit();
                }
            }, 500);
        });

        // Handle jika user menekan enter di search box
        filterForm.addEventListener('submit', function(e) {
            if (searchInput.value.trim() === '') {
                const kategori = filterForm.querySelector('select[name="kategori"]').value;
                if (kategori === '') {
                    e.preventDefault(); // Mencegah submit jika keduanya kosong
                    window.location.href = "{{ route('mobile.form_belanja_cepat.index') }}"; // Arahkan ke URL bersih
                }
            }
        });
    });
</script>
@endpush