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

    .image-banner {
        width: 90%;
        max-width: 900px;
        height: 250px;
        margin: 40px auto 0 auto;
        overflow: hidden;
        border-radius: 5px;
        box-shadow: 2px 8px 46px rgba(0, 0, 0, 0.34);
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        position: relative;
    }

    .image-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
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
        box-shadow: 0 4px 10px rgba(0, 128, 255, 0.19);
        border: 0.1px solid rgba(0, 0, 0, 0.08);
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
        /* perbesar dari sebelumnya, misal 210px */
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9f9f9;
        border-radius: 10px 10px 0 0;
    }

    .produk-img img {
        max-height: 185px !important;
        /* perbesar dari sebelumnya, misal 185px */
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

        .image-banner {
            height: 120px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .image-banner img {
            object-fit: contain;
        }

        #filterForm {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 4px !important;
            width: 100%;
            flex-wrap: wrap !important;
            /* Biar X bisa turun kalau sempit */
        }

        #filterForm input.form-control,
        #filterForm select.form-select {
            font-size: 0.53rem !important;
            /* Lebih kecil lagi */
            padding: 2px 5px !important;
            /* Ramping */
            height: 26px !important;
            /* Ramping */
            border-radius: 7px !important;
            flex: 1 1 0 !important;
            min-width: 0 !important;
        }

        #filterForm select.form-select {
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            max-width: 34vw !important;
            /* Lebih kecil, biar ga nabrak */
        }

        #filterForm .btn-danger {
            width: 22px !important;
            /* Lebih kecil */
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
            /* Agar tidak ada background aneh */
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

        .image-banner {
            max-width: 100% !important;
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .container-fluid,
        .produk-grid {
            max-width: 100vw !important;
            width: 100vw !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin: 0 auto !important;
        }

        .produk-grid {
            grid-template-columns: repeat(5, 1fr) !important;
        }

        .produk-nama {
            font-size: 0.78rem !important;
            min-height: 12px;
        }

        .produk-desc,
        .produk-info-row,
        .produk-harga,
        .produk-stok {
            font-size: 0.59rem !important;
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
            right: 10px;
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
    <a href="#" class="btn btn-light border shadow-sm d-flex align-items-center justify-content-center" style="min-width:36px; min-height:36px; margin-left:8px; margin-top:0px;">
        <i class="bi bi-cart" style="font-size:1.2rem;"></i>
    </a>
</div>

{{-- Banner --}}
<div class="image-banner mb-4">
    <img src="{{ asset('storage/logo/Spanduk KZ Family 2.png') }}" alt="Banner Image">
</div>

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
        document.querySelectorAll('.produk-card').forEach(function(card) {
            card.addEventListener('click', function(e) {
                // Arahkan ke detail di semua device (tidak perlu cek mobile lagi)
                window.location = this.getAttribute('data-detail-url');
            });
            // Tambah tab index biar bisa diakses pakai keyboard (opsional)
            card.setAttribute('tabindex', '0');
            card.style.outline = 'none';
            card.addEventListener('keydown', function(e) {
                if (e.key === "Enter" || e.key === " ") {
                    window.location = this.getAttribute('data-detail-url');
                }
            });
        });
    });
    document.getElementById('kategoriSelect').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
</script>
@endpush