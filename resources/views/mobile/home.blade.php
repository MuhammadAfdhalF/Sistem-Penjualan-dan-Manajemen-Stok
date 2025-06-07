@extends('layouts.template_mobile')
@section('title', 'Halaman Home - KZ Family')

@push('head')
<style>
    body {
        background: #ffff;

        background-image: url('{{ asset("storage/logo/back_biru.png") }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        margin: 0;
        padding: 0;
        font-family: 'Inter', Arial, sans-serif;
    }

    /* Optional: Add media query for smaller devices */
    @media (max-width: 768px) {
        body {
            background-size: contain;
            /* Or adjust as needed for mobile */
        }
    }

    .greet-card {
        background: rgba(255, 255, 255, 0.5);
        /* 0.5 = 50% opacity */
        padding: 22px 18px 12px 18px;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
        position: relative;
        margin-bottom: 8px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        backdrop-filter: blur(5px);
        /* optional: efek blur untuk elemen di belakang */
    }

    .greet-logo {
        width: 56px;
        height: 56px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px #0001;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        overflow: hidden;
    }

    .greet-main {
        flex: 1;
    }

    .greet-title {
        font-size: 1.14rem;
        font-weight: 700;
        letter-spacing: 0.4px;
        margin-bottom: 2px;
    }

    .greet-subtitle {
        font-size: 1.02rem;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .greet-desc {
        font-size: 0.96rem;
        color:rgb(14, 0, 0);
        margin-bottom: 0;
        letter-spacing: 0.2px;
    }

    /* Sembunyikan greet-card di desktop */
    @media (min-width: 769px) {
        .greet-card {
            display: none !important;
        }
    }


    /* Cart Top Button */
    .cart-top-btn {
        background: #ffff;
        border: 0.5px solid rgba(0, 0, 0, 0.5);
        border-radius: 8px;
        padding: 7px 9px;
        box-shadow: 0 6px 32px 0 rgba(30, 40, 60, 0.22), 0 2px 8px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        min-height: 36px;
        margin-left: 8px;
        margin-top: 20px;
        transition: box-shadow 0.15s;
        cursor: pointer;
    }

    .cart-top-btn svg {
        width: 22px;
        height: 22px;
        color: #000000;
    }

    /* Search Row */
    .search-row {
        margin: 13px 16px 14px 16px;
        display: flex;
        gap: 8px;
        /* Memberikan ruang antar elemen */
        border-radius: 6px;
        padding-bottom: 4px;
        padding-top: 4px;
        align-items: center;
        width: 100%;
        /* Pastikan elemen menggunakan lebar penuh */
        flex-wrap: nowrap;
        /* Pastikan elemen tetap dalam satu baris */
    }

    .search-row input[type="text"] {
        flex-grow: 2;
        /* Memastikan input pencarian mengambil lebih banyak ruang */
        min-width: 0;
        /* Menghindari elemen melebihi batas */
        padding: 10px 12px;
        border-radius: 7px;
        box-shadow: 0 0px 8px 0 rgba(30, 40, 60, 0.22), 0 0px 2px rgba(0, 0, 0, 0.15);
        font-size: 1rem;
        outline: none;
        background: #fafdff;
        border: 0.5px solid rgba(0, 0, 0, 0.5);
    }

    .search-row select {
        flex-grow: 1;
        /* Dropdown kategori lebih kecil dari input pencarian */
        min-width: 0;
        max-width: 150px;
        /* Lebar maksimal untuk dropdown kategori */
        padding: 10px 12px;
        border-radius: 7px;
        box-shadow: 0 0px 8px 0 rgba(30, 40, 60, 0.22), 0 0px 2px rgba(0, 0, 0, 0.15);
        font-size: 1rem;
        background: #fafdff;
        border: 0.5px solid rgba(0, 0, 0, 0.5);
    }

    .search-row button,
    .search-row a {
        flex-shrink: 0;
        /* Tombol tidak akan mengecil */
        padding: 6px 8px;
        border-radius: 8px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-width: 36px;
        min-height: 36px;
    }

    /* Tombol Submit */
    .search-row button {
        background: rgb(0, 123, 255);
        color: #fff;
        font-size: 18px;
        /* Ukuran ikon */
    }

    /* Tombol Hapus Filter */
    .search-row a {
        background: rgb(220, 53, 69);
        color: #fff;
        font-size: 18px;
        /* Ukuran ikon */
    }

    /* Responsif untuk Mobile */
    @media (max-width: 600px) {
        .search-row {
            flex-wrap: nowrap;
            /* Pastikan elemen tetap dalam satu baris */
            gap: 4px;
            /* Mengurangi jarak antar elemen untuk mobile */
        }

        .search-row input[type="text"],
        .search-row select {
            flex: 1;
            /* Membuat input dan select mengisi ruang secara proporsional */
            font-size: 0.9rem;
            /* Menyesuaikan ukuran font di mobile */
        }

        .search-row button,
        .search-row a {
            padding: 6px;
            /* Menyesuaikan ukuran tombol di mobile */
            font-size: 16px;
            /* Ukuran font tombol di mobile */
            min-width: 36px;
            /* Menjaga ukuran tombol tetap kecil */
        }

        /* Menghindari tombol X untuk turun ke bawah */
        .search-row a {
            white-space: nowrap;
            /* Menjaga agar tombol X tidak turun ke bawah */
        }
    }

    /* Produk Grid */
    .produk-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        /* Default untuk mobile (2 kolom) */
        gap: 6px;
        padding: 0 6px 12px 6px;
        background: #ffff;
    }

    @media (min-width: 768px) {
        .produk-grid {
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
            /* 6 kolom pada layar besar (website) */
        }
    }

    .produk-card {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        /* Shadow lebih tajam */
        overflow: hidden;
        border: 2px solid rgba(0, 0, 0, 0.1);
        /* Border ditambahkan untuk ketajaman */
        display: flex;
        flex-direction: column;
        margin-bottom: 0;
        padding: 6px;
        box-sizing: border-box;
        min-height: 200px;
        height: auto;
        transition: box-shadow 0.3s ease;
        /* Efek transisi saat hover */
    }


    .produk-img {
        position: relative;
        overflow: hidden;
    }

    .produk-img img {
        width: 100%;
        display: block;
    }

    .produk-actions {
        position: absolute;
        left: 50%;
        bottom: 12px;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
        z-index: 2;
    }

    /* Desktop: hover, Mobile/JS: active */
    .produk-img:hover .produk-actions,
    .produk-img.active .produk-actions {
        opacity: 1;
        pointer-events: auto;
    }


    .produk-action-btn {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 50%;
        background: #ed2d34;
        /* default: merah */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.14);
        cursor: pointer;
        outline: none;
        transition: transform 0.2s, background 0.2s;
    }

    /* Icon Fullscreen = merah (default) */
    .produk-fullscreen-btn {
        background: #ed2d34;
    }

    /* Icon Eye = biru */
    .produk-eye-btn {
        background: #135291;
    }

    .produk-action-btn svg {
        width: 22px;
        height: 22px;
        stroke: #fff;
    }


    /* Nama Produk */
    .produk-nama {
        font-size: 1rem;
        /* Ukuran font lebih besar untuk nama produk */
        font-weight: 600;
        margin: 12px 6px 2px 6px;
        color: #232323;
        height: auto;
    }

    /* Deskripsi Produk */
    .produk-desc {
        font-size: 0.9rem;
        /* Ukuran font lebih besar untuk deskripsi produk */
        color: #666;
        margin: 0 6px 6px 6px;
        line-height: 1.3;
        overflow: visible;
    }

    .produk-info-row {
        margin-top: auto;
        /* Ini akan mendorong elemen ke bawah */
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 6px 4px 6px;
        font-size: 0.9rem;
        color: #242424;
    }

    .produk-harga,
    .produk-stok {
        font-size: 0.9rem;
        /* Ukuran font lebih besar untuk harga dan stok */
        color: #888;
    }

    /* Bagian Mobile tetap tidak berubah */
    @media (max-width: 600px) {
        .produk-card {
            padding: 4px;
            /* Mengurangi padding pada perangkat mobile */
            min-height: 160px;
            /* Menambah min-height agar card cukup tinggi pada mobile */
        }

        .produk-harga,
        .produk-stok {
            font-size: 0.7rem;
            /* Ukuran font lebih kecil pada perangkat mobile */
        }

        .produk-img {
            font-size: 0.8rem;
            /* Ukuran font gambar lebih kecil pada perangkat mobile */
        }

        .produk-nama {
            font-size: 0.7rem;
            /* Ukuran font nama lebih kecil pada perangkat mobile */
        }

        .produk-desc {
            font-size: 0.7rem;
            /* Ukuran font deskripsi lebih kecil pada perangkat mobile */
        }

        .produk-info-row {
            font-size: 0.7rem;
            /* Ukuran font bar informasi lebih kecil pada perangkat mobile */
        }
    }


    .image-banner {
        width: 90%;
        /* Lebar cukup besar, agar tampak full */
        max-width: 900px;
        /* Biar di desktop tetap proporsional */
        height: 250px;
        /* Tetap fixed height */
        margin: 40px auto 0 auto;
        /* Tengah otomatis! (top 40px agar tidak nempel header) */
        overflow: hidden;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
        display: flex;
        /* Supaya child img gampang di-center-kan */
        align-items: center;
        /* Center vertikal img */
        justify-content: center;
        /* Center horizontal img */
        background: #fff;
        position: relative;
    }

    .image-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* Bisa juga coba 'contain' jika ingin selalu penuh dan tidak crop */
        display: block;
    }



    .image-banner:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        /* More prominent shadow on hover */
    }



    @media (max-width: 600px) {
        .image-banner {
            height: 100px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            /* Lighter shadow for mobile */
            border-radius: 4px;
            /* Smaller radius for mobile */
        }

        .image-banner:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            /* Less pronounced hover effect on mobile */
        }

        .image-banner img {
            object-fit: contain;
        }
    }
</style>
@endpush



@section('content')
<div class="greet-card">
    <div style="display: flex; align-items: center;">
        <!-- LOGO tanpa box -->
        <img src="{{ asset('storage/logo/LogoKZ_transparant.png') }}" alt="Logo KZ" style="width:80px; height:auto; margin-right: 12px;">
        <div class="greet-main" style="display: flex; flex-direction: column; justify-content: center; height: 80px;">
            <div class="greet-title" style="margin-bottom:3px;">ACuyyy</div>
            <div class="greet-subtitle" style="font-size:0.97rem; color:#6b6b6b; font-weight:500;">Katalog produk</div>
        </div>
    </div>

    <button class="cart-top-btn">
        <!-- Keranjang icon -->
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="9" cy="21" r="1" />
            <circle cx="20" cy="21" r="1" />
            <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>
</div>

<!-- Gambar Persegi Panjang -->
<div class="image-banner">
    <img src="{{ asset('storage/logo/Spanduk KZ Family 2.png') }}" alt="Banner Image">
</div>

<!-- Filter dan Pencarian Produk -->
<!-- Filter dan Pencarian Produk -->
<div class="search-row">
    <form id="filterForm" method="GET" action="{{ route('mobile.home.index') }}" class="d-flex align-items-center w-100">
        <!-- Input Pencarian Produk -->
        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Cari Produk diinginkan..."
            value="{{ request()->search }}"
            autocomplete="off"
            id="searchInput">

        <!-- Filter Kategori -->
        <select
            name="kategori"
            class="form-select ms-2"
            style="min-width: 150px;"
            id="kategoriSelect">
            <option value="">-- Semua Kategori --</option>
            @foreach($listKategori as $kategori)
            <option value="{{ $kategori }}" {{ request()->kategori == $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
            @endforeach
        </select>

        <!-- Tombol Hapus Filter (X Emoji) - Untuk kategori atau pencarian -->
        @if(request()->kategori || request()->search)
        <span onclick="window.location='{{ route('mobile.home.index')}}'" style="display: inline-flex; justify-content: center; align-items: center; padding: 6px 8px; border-radius: 8px; font-size: 20px; cursor: pointer;">
            ❌
        </span>
        @endif
    </form>
</div>







<!-- Produk Grid -->
<div class="produk-grid">
    @foreach($produk as $item)
    <div
        class="produk-card"
        data-detail-url="{{ route('mobile.detail_produk.index', $item->id) }}"
        style="cursor:pointer;">
        <div class="produk-img">
            <img src="{{ asset('storage/gambar_produk/' . $item->gambar) }}" alt="{{ $item->nama_produk }}" loading="lazy">
            <div class="produk-actions">
                <button
                    class="produk-action-btn produk-eye-btn d-none d-md-flex"
                    onclick="window.location.href=this.closest('.produk-card').getAttribute('data-detail-url'); event.stopPropagation();"
                    title="Lihat Detail Produk">
                    <!-- Eye icon -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="12" rx="8" ry="5" />
                        <circle cx="12" cy="12" r="2.5" fill="#fff" stroke="none" />
                        <circle cx="12" cy="12" r="1.2" fill="#222" stroke="none" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="produk-nama">{{ $item->nama_produk }}</div>
        <div class="produk-desc">{{ $item->deskripsi }}</div>

        <div class="produk-info-row">
            <span class="produk-harga">
                @php
                // Ambil maksimal 3 satuan beserta harga untuk produk ini
                $hargaList = $item->satuans->take(3)->map(function($satuan) use ($item) {
                $harga = $item->hargaProduks->where('satuan_id', $satuan->id)->first();
                return $harga
                ? 'Rp. ' . number_format($harga->harga, 0, ',', '.') . '/' . $satuan->nama_satuan
                : null;
                })->filter()->toArray();
                @endphp
                {!! implode('<br>', $hargaList) !!}
            </span>
            <span class="produk-stok">{{ $item->stok_bertingkat }}</span>
        </div>

    </div>

    @endforeach
</div>

@endsection


<script>
    document.addEventListener('DOMContentLoaded', function() {
        function isMobile() {
            return window.innerWidth <= 768;
        }

        // Klik card: buka detail di mobile
        document.querySelectorAll('.produk-card').forEach(function(card) {
            card.addEventListener('click', function(e) {
                if (isMobile()) {
                    window.location = this.getAttribute('data-detail-url');
                }
            });
        });

        // Tombol mata: desktop (biar tetap stopPropagation, mobile gak pengaruh)
        document.querySelectorAll('.produk-eye-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                if (!isMobile()) {
                    // biarkan href di tombol
                }
                e.stopPropagation();
            });
        });

        // === FILTER OTOMATIS ===

        // Otomatis submit saat ganti kategori
        var kategoriSelect = document.getElementById('kategoriSelect');
        if (kategoriSelect) {
            kategoriSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }

        // Otomatis submit saat search (debounce 600ms)
        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            let timeout = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    searchInput.form.submit();
                }, 600);
            });
            // Submit juga kalau tekan Enter
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchInput.form.submit();
                }
            });
        }
    });
</script>