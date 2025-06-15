@extends('layouts.template_mobile')
@section('title', 'Form Belanja Cepat - KZ Family')

@push('head')
<style>
    body {
        background-color: #f8f9fa;
    }

    .product-card {
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 1rem;
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

    @media (max-width: 576px) {
        .filter-wrapper {
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            justify-content: center !important;
        }

        .filter-wrapper .filter-input {
            flex: 0 0 65% !important;
        }

        .filter-wrapper .filter-select {
            flex: 0 0 30% !important;
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
</style>
@endpush
@section('content')
<div class="container-fluid px-0 px-lg-3 py-3 desktop-container">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 ms-3 mt-3 d-block d-lg-none">
        <div>
            <h6 class="fw-bold mb-1 text-body">Toko KZ Family</h6>
            <small class="text-muted">Form Belanja Cepat</small>
        </div>
        <div class="me-3">
            <button class="btn bg-white shadow rounded-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-cart text-dark" style="font-size: 1.2rem;"></i>
            </button>
        </div>
    </div>


    <!-- Banner Welcome -->
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


    <!-- Filter -->
    <!-- Filter -->
    <div class="d-flex flex-wrap justify-content-between gap-1 mb-4 w-100 filter-wrapper">
        <div class="d-flex align-items-center shadow-sm px-3 flex-grow-1 filter-input"
            style="background-color: #fff; height: 44px; border: 1px solid rgba(0, 0, 0, 0.38); border-radius: 8px;">
            <span class="me-2" style="font-size: 1rem;">🔍</span>
            <input type="text"
                class="form-control border-0 shadow-none p-0"
                placeholder="Cari Produk diinginkan...."
                style="font-size: clamp(0.75rem, 1.5vw, 1rem); background-color: transparent;">
        </div>
        <div class="filter-select" style="width: 180px;">
            <select class="form-select shadow-sm w-100"
                style="height: 44px; font-size: clamp(0.75rem, 1.5vw, 0.95rem); border: 1px solid rgba(0, 0, 0, 0.38); border-radius: 8px;">
                <option selected>-- Semua Kategori --</option>
                <option>Minuman</option>
                <option>Makanan</option>
                <option>Alat Tulis</option>
            </select>
        </div>
    </div>




    <!-- Layout Produk & Checkout -->
    <div class="row">
        <!-- Produk (kiri) -->
        <div class="col-lg-8">
            @for($i = 0; $i < 4; $i++)
                <div class="card product-card p-3 mb-3">
                <div class="d-flex align-items-start gap-3">
                    <input type="checkbox" class="custom-check mt-1">
                    <img src="{{ asset('storage/gambar_produk/contoh.png') }}" class="product-image" alt="Produk">
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold text-body mb-1 text-wrap">Nama Product</h6>
                        <div class="text-muted small mb-1">Rp. 50.000</div>
                        <div class="text-muted small mb-2">Tersedia : 5 Slof 2 Bks</div>

                        <div class="jumlah-satuan-wrapper text-end">
                            <div class="row gx-2 align-items-center satuan-group mb-2 justify-content-end">
                                <!-- Input Jumlah dengan + - -->
                                <div class="col-auto">
                                    <div class="input-group input-group-sm" style="border: none;">
                                        <button class="btn btn-sm bg-light border-0 btn-minus" type="button" style="font-size: 0.75rem;">−</button>
                                        <input type="text"
                                            class="form-control text-center jumlah-input bg-light border-0"
                                            placeholder="0"
                                            value=""
                                            style="width: 45px; font-size: 0.75rem;">
                                        <button class="btn btn-sm bg-light border-0 btn-plus" type="button" style="font-size: 0.75rem;">+</button>
                                    </div>
                                </div>

                                <!-- Dropdown Satuan -->
                                <div class="col-auto" style="width: 80px;">
                                    <select name="satuan[]" class="form-select form-select-sm border-0 bg-light" style="font-size: 0.75rem;">
                                        <option value="bks">Bks</option>
                                        <option value="slof">Slof</option>
                                    </select>
                                </div>

                                <!-- Tombol Tambah -->
                                <div class="col-auto" style="width: 40px;">
                                    <button type="button" class="btn btn-sm btn-light text-success tambah-jumlah w-100" style="font-size: 0.75rem;">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>

                                <!-- Tombol Hapus -->
                                <div class="col-auto" style="width: 40px;">
                                    <button type="button" class="btn btn-sm btn-light text-danger hapus-jumlah w-100 d-none" style="font-size: 0.75rem;">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
        </div>
        @endfor
    </div>


    <!-- Checkout (kanan desktop saja) -->
    <div class="col-lg-4 d-none d-lg-block mt-3 mt-lg-0">
        <div class="bg-white p-4" style="box-shadow: 0 12px 42px rgba(0, 0, 0, 0.08), 0 3px 10px rgba(0, 0, 0, 0.04); border-radius: 18px;">
            <div class="fw-semibold text-dark mb-1 fs-6">Total Produk : <span class="text-dark fw-bold">15 item</span></div>
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <span class="text-secondary fs-6">Total</span>
                <span class="fw-bold fs-5" style="color: #135291;">Rp 50.000</span>
            </div>
            <button class="btn w-100 fw-bold" style="border-radius: 12px; font-size: 1rem; padding: 12px 0; background-color: #135291; color: white;">
                CHECKOUT !!!
            </button>

        </div>
    </div>


    <!-- Footer Mobile Checkout -->
    <div class="fixed-bottom bg-white border-top shadow-sm p-2 d-lg-none" style="bottom: 65px; z-index: 102;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-start">
                <small class="text-muted d-block">Total Produk : <strong class="text-dark">15 produk</strong></small>
                <small class="text-muted d-block">Total : <strong class="text-dark">Rp. 50.000</strong></small>
            </div>
            <button class="btn btn-primary fw-bold px-4"
                style="background-color: #135291; color: white; border-radius: 10px; margin-right: 10px;">
                Checkout !!!
            </button>
        </div>
    </div>

    @endsection

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function bindEvents(group) {
                const tambahBtn = group.querySelector('.tambah-jumlah');
                const hapusBtn = group.querySelector('.hapus-jumlah');

                tambahBtn.addEventListener('click', function() {
                    const clone = group.cloneNode(true);
                    const wrapper = group.closest('.jumlah-satuan-wrapper');

                    // Bersihkan input
                    clone.querySelector('.jumlah-input').value = 0;

                    // Tampilkan tombol hapus untuk clone
                    clone.querySelector('.hapus-jumlah').classList.remove('d-none');

                    // Tambah clone
                    wrapper.appendChild(clone);
                    bindEvents(clone);
                });

                hapusBtn.addEventListener('click', function() {
                    const wrapper = group.closest('.jumlah-satuan-wrapper');
                    if (wrapper.querySelectorAll('.satuan-group').length > 1) {
                        group.remove();
                    }
                });
            }

            document.querySelectorAll('.jumlah-satuan-wrapper .satuan-group').forEach(bindEvents);
        });

        document.querySelectorAll('.jumlah-satuan-wrapper').forEach(wrapper => {
            wrapper.addEventListener('click', function(e) {
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
            });
        });
    </script>
    @endpush