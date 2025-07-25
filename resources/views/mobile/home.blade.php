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

    @media (max-width: 768px) {
        body {
            background-size: contain;
        }
    }

    .greet-card {
        background: rgba(255, 255, 255, 0.5);
        padding: 22px 18px 12px 18px;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
        margin-bottom: 8px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        backdrop-filter: blur(5px);
    }

    @media (min-width: 769px) {
        .greet-card {
            display: none !important;
        }
    }

    /* Styles for the banner carousel container */
    .image-banner-carousel-container {
        /* MENGGANTI .image-banner */
        width: 90%;
        max-width: 900px;
        height: 250px;
        margin: 40px auto 0 auto;
        overflow: hidden;
        border-radius: 5px;
        box-shadow: 2px 8px 46px rgba(0, 0, 0, 0.34);
        display: flex;
        /* keep flex for centering if content is smaller */
        align-items: center;
        justify-content: center;
        background: #fff;
        position: relative;
    }

    .image-banner-carousel-container .carousel-item img {
        /* target gambar di dalam carousel */
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* default to cover */
        display: block;
    }


    .produk-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 0 8px 16px 8px;
        background: #ffff;
    }

    @media (min-width: 768px) {
        .produk-grid {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    .produk-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 8px;
        box-shadow: 0 0px 2px #000000;
        border: 0.1px solid rgba(0, 0, 0, 0);
        display: flex;
        flex-direction: column;
        margin-bottom: 0;
        padding: 8px;
        min-height: 190px;
        cursor: pointer;
        transition: box-shadow 0.3s;
        height: 100%;
    }

    .produk-img {
        height: 210px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9f9f9;
        border-radius: 10px 10px 0 0;
    }

    .produk-img img {
        max-height: 185px !important;
        width: auto !important;
        object-fit: contain;
        display: block;
        margin: auto;
    }

    .produk-nama {
        font-size: 1rem;
        font-weight: 600;
        margin: 10px 6px 2px 6px;
        color: #232323;
        min-height: 36px;
    }

    .produk-desc {
        font-size: 0.9rem;
        color: #666;
        margin: 0 6px 6px 6px;
        line-height: 1.3;
    }

    .produk-info-row {
        margin-top: auto;
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
        color: #888;
    }

    @media (max-width: 600px) {
        .hover-eye {
            display: none !important;
        }

        /* Responsive styles for the banner carousel container */
        .image-banner-carousel-container {
            /* MENGGANTI .image-banner */
            height: 120px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .image-banner-carousel-container .carousel-item img {
            /* target gambar di dalam carousel */
            object-fit: contain !important;
            /* enforce contain for mobile */
        }

        #filterForm {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 4px !important;
            width: 100%;
            flex-wrap: wrap !important;
        }

        #filterForm input.form-control,
        #filterForm select.form-select {
            font-size: 0.6rem !important;
            padding: 2px 5px !important;
            height: 35px !important;
            border-radius: 7px !important;
            flex: 1 1 0 !important;
            min-width: 0 !important;
        }

        #filterForm select.form-select {
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            max-width: 50vw !important;
        }

        #filterForm .btn-danger {
            width: 22px !important;
            height: 22px !important;
            min-width: 0 !important;
            min-height: 0 !important;
            font-size: 1rem !important;
            padding: 0 !important;
            border-radius: 6px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            flex: none !important;
        }

        #filterForm .btn-danger i {
            font-size: 0.92rem !important;
        }

        /* Hilangkan margin/padding row-col di mobile */
        #filterForm .row,
        #filterForm .col,
        #filterForm .col-auto {
            padding: 0 !important;
            margin: 0 !important;
        }

        #filterForm input.form-control,
        #filterForm select.form-select {
            border: 1.2px solid #bdbdbd !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        .produk-card {
            padding: 3px 2px !important;
            min-height: 120px !important;
        }

        .produk-img {
            height: 150px !important;
        }

        .produk-img img {
            max-height: 130px !important;
        }

        .produk-nama {
            font-size: 0.88rem !important;
            min-height: 18px;
            color: #232323 !important;
            font-weight: 600;
        }

        .produk-desc {
            font-size: 0.71rem !important;
            min-height: 14px;
            color: #232323 !important;
        }

        .produk-info-row {
            font-size: 0.68rem !important;
            color: #232323 !important;
            padding-bottom: 0;
        }

        .produk-harga,
        .produk-stok {
            font-size: 0.68rem !important;
            color: #232323 !important;
        }
    }


    @media (max-width: 1000px) and (orientation: landscape) {
        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .greet-card {
            display: flex !important;
        }

        .produk-grid {
            grid-template-columns: repeat(4, 1fr) !important;
        }

        .produk-card {
            min-height: 170px !important;
            padding: 9px 6px !important;
        }

        .produk-img {
            height: 115px !important;
        }

        .produk-img img {
            max-height: 100px !important;
        }

        .produk-nama,
        .produk-desc,
        .produk-info-row {
            font-size: 0.89rem !important;
        }

        #filterForm {
            margin-left: 10px !important;
            gap: 10px !important;
        }

        form#filterForm input.form-control,
        form#filterForm select.form-select {
            font-size: 0.85rem !important;
            height: 32px !important;
            border-radius: 12px !important;
            padding: 2px 14px !important;
            min-width: 0 !important;
            max-width: 240px !important;
            width: 210px !important;
        }

        form#filterForm select.form-select {
            text-align: center !important;
            text-align-last: center !important;
            max-width: 180px !important;
            min-width: 110px !important;
            width: 150px !important;
        }

        #filterForm .btn-danger {
            width: 42px !important;
            height: 42px !important;
            font-size: 0.1rem !important;
            border-radius: 13px !important;
            margin-left: 7px !important;
        }
    }


    @media (min-width: 768px) {

        #filterForm input.form-control:hover,
        #filterForm input.form-control:focus,
        #filterForm select.form-select:hover,
        #filterForm select.form-select:focus {
            box-shadow: 0 2px 12px rgb(19, 82, 145, 0.18);
            border-color: rgb(19, 82, 145) !important;
            z-index: 2;
            transition: box-shadow .16s, border-color .16s;
        }

        #filterForm .btn-danger:hover,
        #filterForm .btn-danger:focus {
            box-shadow: 0 2px 12px rgb(19, 82, 145, 0.19);
            border-color: rgb(19, 82, 145) !important;
            outline: none;
            z-index: 2;
            transition: box-shadow .16s, border-color .16s;
        }

        #filterForm {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            background: none;
            box-shadow: none;
            padding: 0;
            margin-bottom: 18px;
        }

        #filterForm input.form-control,
        #filterForm select.form-select {
            border: 1.5px solid #222 !important;
            border-radius: 13px !important;
            height: 38px !important;
            font-size: 1rem !important;
            background: #fff !important;
            box-shadow: none !important;
            min-width: 140px;
            max-width: 350px;
            transition: border-color .18s;
        }

        #filterForm input.form-control:focus,
        #filterForm select.form-select:focus {
            border-color: #1976d2 !important;
            outline: none !important;
        }

        #filterForm .btn-danger {
            width: 38px !important;
            height: 38px !important;
            min-width: 0 !important;
            min-height: 0 !important;
            font-size: 1.18rem !important;
            padding: 0 !important;
            border-radius: 13px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: 1.5px solid #222 !important;
            background: #dc3545 !important;
            color: #fff !important;
            transition: border-color .18s;
        }

        #filterForm .btn-danger:focus {
            border-color: #1976d2 !important;
            outline: none !important;
        }

        #kategoriSelect {
            max-width: 250px !important;
            min-width: 250px !important;
            width: 100% !important;
            text-align: center;
        }

        .produk-card {
            cursor: pointer !important;
            position: relative;
            transition: box-shadow 0.25s;
        }

        .produk-card:hover,
        .produk-card:focus {
            box-shadow: 0 4px 20px rgb(19, 82, 145);
            z-index: 2;
        }

        /* Icon mata muncul di hover/focus */
        .produk-card .hover-eye {
            display: none;
            position: absolute;
            top: 8px;
            bottom: unset;
            /* ensure it's not affected by previous rules */
            right: 10px;
            left: unset;
            /* ensure it's not affected by previous rules */
            z-index: 3;
            font-size: 1.24rem;
            color: #4f4f4f;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 100px;
            box-shadow: 0 1px 6px rgba(0, 21, 255, 0.07);
            padding: 2px 6px;
            transition: opacity .18s;
            pointer-events: none;
        }

        .produk-card:hover .hover-eye,
        .produk-card:focus .hover-eye {
            display: block;
            opacity: 1;
        }
    }


    /* --- TABLET PORTRAIT: 3 kolom (contoh iPad 810x1080) --- */
    @media (min-width: 601px) and (max-width: 1024px) and (orientation: portrait) {

        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }


        .greet-card {
            display: flex !important;
        }

        .produk-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }

        .produk-card {
            min-height: 180px !important;
            padding: 10px 7px !important;
        }

        .produk-img {
            height: 130px !important;
        }

        .produk-img img {
            max-height: 110px !important;
        }

        .produk-nama,
        .produk-desc,
        .produk-info-row {
            font-size: 0.96rem !important;
        }
    }

    /* --- TABLET LANDSCAPE: 4 kolom --- */
    @media (min-width: 900px) and (max-width: 1200px) and (orientation: landscape) {
        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }


        .greet-card {
            display: flex !important;
        }

        .produk-grid {
            grid-template-columns: repeat(4, 1fr) !important;
        }

        .produk-card {
            min-height: 170px !important;
            padding: 9px 6px !important;
        }

        .produk-img {
            height: 115px !important;
        }

        .produk-img img {
            max-height: 100px !important;
        }

        .produk-nama,
        .produk-desc,
        .produk-info-row {
            font-size: 0.89rem !important;
        }
    }

    /* Banner lebih pendek, object-fit: contain */
    @media (max-width: 1024px) {
        .image-banner-carousel-container {
            /* MENGGANTI .image-banner */
            height: 120px !important;
            min-height: 90px !important;
            max-height: 150px !important;
            margin: 16px auto 0 auto !important;
            border-radius: 12px !important;
            box-shadow: 0 1.5px 9px rgba(0, 0, 0, 0.12);
        }

        .image-banner-carousel-container .carousel-item img {
            /* target gambar di dalam carousel */
            object-fit: contain !important;
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        /* Banner lebih pendek, object-fit: contain (redundant, but keeping structure if you had another one) */
        @media (max-width: 1024px) {
            .image-banner-carousel-container {
                /* MENGGANTI .image-banner */
                height: 170px !important;
                /* Naikkan height banner */
                min-height: 150px !important;
                max-height: 220px !important;
                margin-bottom: 18px !important;
                /* Lebih banyak jarak bawah */
                border-radius: 13px !important;
            }

            .image-banner-carousel-container .carousel-item img {
                /* target gambar di dalam carousel */
                object-fit: contain !important;
                width: 100% !important;
                height: 100% !important;
            }

            /* Beri jarak antara filter & produk */
            #filterForm {
                margin-bottom: 18px !important;
            }

            /* Jika produk grid terlalu nempel atas, beri margin top */
            .produk-grid {
                margin-top: 0 !important;
            }
        }
    }
</style>
@endpush

@section('content')

{{-- Greeting Card (mobile only) --}}
<div class="greet-card">
    <div style="display: flex; align-items: center;">
        <img src="{{ asset('storage/logo/LogoKZ_transparant.png') }}" alt="Logo KZ" style="width:70px; height:auto; margin-right: 12px;">
        <div style="display: flex; flex-direction: column; justify-content: center; height: 70px;">
            <div class="fw-bold" style="margin-bottom:3px;">Toko KZ Family</div>
            <div class="text-muted" style="font-size:0.95rem;">Katalog produk</div>
        </div>
    </div>
    <a href="{{ route('mobile.keranjang.index') }}" class="btn btn-outline-secondary mt-3">
        <i class="bi bi-cart text-dark"></i> </a>

</div>

{{-- Banner Carousel --}}
{{-- Mengganti div .image-banner statis dengan carousel dinamis --}}
@if($banners->isNotEmpty())
<div id="bannerCarousel" class="carousel slide image-banner-carousel-container mb-4" data-bs-ride="carousel" data-bs-interval="2000">
    <div class="carousel-indicators">
        @foreach($banners as $index => $banner)
        <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="{{ $index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
        @endforeach
    </div>
    <div class="carousel-inner rounded">
        @foreach($banners as $banner)
        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
            {{-- Gambar di dalam carousel akan menggunakan styles dari .image-banner-carousel-container .carousel-item img --}}
            <img src="{{ asset('storage/' . $banner->gambar_url) }}" class="d-block w-100" alt="{{ $banner->nama_banner }}">
            {{-- Jika ada kolom link_url di banner, Anda bisa membuatnya clickable di sini --}}
            {{-- @if($banner->link_url)
                        <a href="{{ $banner->link_url }}" class="stretched-link"></a>
            @endif --}}
        </div>
        @endforeach
    </div>
    @if($banners->count() > 1) {{-- Hanya tampilkan kontrol jika ada lebih dari 1 banner --}}
    <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
    @endif
</div>
@else
{{-- Opsional: Placeholder jika tidak ada banner aktif --}}
<div class="image-banner-carousel-container mb-4 d-flex align-items-center justify-content-center text-muted">
    Tidak ada promosi menarik saat ini.
</div>
@endif

{{-- Search & Filter --}}
<div class="container-fluid px-3">
    <form id="filterForm" method="GET" action="{{ route('mobile.home.index') }}" class="d-flex align-items-center gap-1 w-100 flex-wrap">
        <input
            type="text"
            name="search"
            class="form-control form-control-sm flex-fill"
            placeholder="🔍 Cari Produk diinginkan.... "
            value="{{ request()->search }}"
            autocomplete="off"
            id="searchInput">
        <select
            name="kategori"
            class="form-select form-select-sm flex-fill"
            id="kategoriSelect">
            <option value="">-- Semua Kategori --</option>
            @foreach($listKategori as $kategori)
            <option value="{{ $kategori }}" {{ request()->kategori == $kategori ? 'selected' : '' }}>{{ $kategori }}</option>
            @endforeach
        </select>
        @if(request()->kategori || request()->search)
        <a href="{{ route('mobile.home.index') }}"
            class="btn btn-danger d-flex align-items-center justify-content-center px-0 reset-btn"
            style="width: 32px; height: 32px; min-width: 32px;">
            <i class="bi bi-x-lg"></i>
        </a>
        @endif
    </form>
</div>

{{-- Produk Grid --}}
<div class="produk-grid mt-3">
    @foreach($produk as $item)
    <div class="produk-card" data-detail-url="{{ route('mobile.detail_produk.index', $item->id) }}">
        <span class="hover-eye">
            <i class="bi bi-eye"></i>
        </span>
        <div class="produk-img">
            <img src="{{ asset('storage/gambar_produk/' . $item->gambar) }}" alt="{{ $item->nama_produk }}">
        </div>
        <div class="produk-nama">
            {{ $item->nama_produk }}
        </div>
        <div class="produk-desc">
            {{ $item->deskripsi }}
        </div>
        <div class="produk-info-row">
            <div class="produk-harga">
                @php
                $hargaList = $item->satuans->take(3)->map(function($satuan) use ($item) {
                $harga = $item->hargaProduks->where('satuan_id', $satuan->id)->first();
                return $harga
                ? 'Rp. ' . number_format($harga->harga, 0, ',', '.') . '/' . $satuan->nama_satuan
                : null;
                })->filter()->toArray();
                @endphp
                {!! implode('<br>', $hargaList) !!}
            </div>
            <div class="produk-stok">
                {{ $item->stok_bertingkat }}
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('body')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Redirect produk-card
        document.querySelectorAll('.produk-card').forEach(function(card) {
            card.addEventListener('click', function(e) {
                window.location = this.getAttribute('data-detail-url');
            });
            card.setAttribute('tabindex', '0');
            card.style.outline = 'none';
            card.addEventListener('keydown', function(e) {
                if (e.key === "Enter" || e.key === " ") {
                    window.location = this.getAttribute('data-detail-url');
                }
            });
        });

        // Submit kategori otomatis saat select berubah
        document.getElementById('kategoriSelect').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        // ✅ Debounce untuk search input (auto submit setelah 500ms)
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (searchInput.value.trim() !== '' || filterForm.querySelector('select[name="kategori"]').value !== '') {
                    filterForm.submit();
                }
            }, 500);
        });
    });
</script>

@endpush