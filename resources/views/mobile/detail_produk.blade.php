@extends('layouts.template_mobile')
@section('title', 'Detail Produk - KZ Family')

@section('content')
<style>
    html,
    body {
        margin: 0;
        padding: 0;
        background: #f7f7f7;
        font-family: 'Inter', Arial, sans-serif;
        font-size: 18px;
        height: 100%;
        min-height: 100%;
    }

    body,
    .flex-main {
        min-height: 100vh;
        height: 100vh;
        width: 100vw;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .container-detail {
        flex: 1 0 auto;
        width: 100vw;
        background: #f7f7f7;
        box-sizing: border-box;
    }

    .footer-fixed {
        flex-shrink: 0;
        position: static;
        width: 100vw;
        background: #fff;
        box-shadow: 0 -2px 16px #bfe0f7a8;
        z-index: 1000;
        padding: 18px 0 18px 0;
        display: flex;
        justify-content: center;
    }

    .btn-keranjang {
        background: #93c8f9;
        color: #222;
        font-weight: bold;
        border: none;
        border-radius: 10px;
        padding: 15px 0;
        font-size: 17px;
        width: 85vw;
        max-width: 420px;
        box-shadow: 0 2px 7px #bfe0f7;
        cursor: pointer;
        transition: background 0.18s;
        margin: 0 auto;
        display: block;
        letter-spacing: 0.5px;
    }

    .btn-keranjang:hover {
        background: #6bb3ea;
    }

    .footer-fixed {
        padding: 10px 0 10px 0;
    }

    /* HIDE FOOTER NAV KHUSUS DI HALAMAN INI (detail produk, mobile) */
    @media (max-width: 768px) {
        .footer-nav {
            display: none !important;
        }
    }

    /* --- SISA CSS untuk header, produk, form sama seperti sebelumnya --- */
    .header-detail {
        display: flex;
        align-items: center;
        padding: 20px 18px 12px 18px;
        background: #cce7fc;
        border-bottom: 2px solid #e0e6ea;
        box-shadow: 0 2px 6px #e8eaf0;
        position: sticky;
        top: 0;
        z-index: 10;
        width: 100vw;
        min-width: 100vw;
    }

    .header-detail svg {
        margin-right: 18px;
        cursor: pointer;
        min-width: 30px;
    }

    .header-titles {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .header-titles .main {
        font-weight: bold;
        font-size: 20px;
        letter-spacing: .1px;
    }

    .header-titles .sub {
        font-weight: bold;
        font-size: 18px;
    }

    .header-titles .desc {
        font-size: 13px;
        color: #878787;
        margin-top: -2px;
    }

    .product-image-area {
        width: 100vw;
        background: #fff;
        border-radius: 0 0 18px 18px;
        box-shadow: 0 2px 10px #e3e8ef;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        aspect-ratio: 1 / 1.1;
        /* area proporsional, tidak terpotong */
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Responsive mobile */
    @media (max-width: 600px) {
        .product-image-area {
            aspect-ratio: 1 / 1;
            max-width: 100vw;
        }
    }

    .product-image-placeholder {
        width: 100%;
        height: 100%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 30px;
        position: absolute;
        left: 0;
        top: 0;
        background: #fff;
    }

    .product-image-placeholder:before,
    .product-image-placeholder:after {
        content: "";
        position: absolute;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        pointer-events: none;
    }

    .product-image-placeholder:before {
        border-top: 1.7px solid #aaa;
        border-left: 1.7px solid #aaa;
        border-right: none;
        border-bottom: none;
        transform: rotate(45deg);
    }

    .product-image-placeholder:after {
        border-top: 1.7px solid #aaa;
        border-left: none;
        border-right: 1.7px solid #aaa;
        border-bottom: none;
        transform: rotate(-45deg);
    }

    .product-image-placeholder span {
        z-index: 2;
        color: #888;
        font-size: 32px;
        position: relative;
    }

    /* Gambar produk */
    .product-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
        position: relative;
        z-index: 3;
    }


    .info-card {
        background: #fff;
        margin: 0;
        border-radius: 0 0 18px 18px;
        box-shadow: 0 2px 12px #e6e6ef;
        padding: 28px 20px 20px 20px;
        border-top: 3px solid #ededf7;
        width: 100vw;
        box-sizing: border-box;

        /* Tambahan agar bottom-row selalu di bawah */
        display: flex;
        flex-direction: column;
        min-height: 210px;
        /* Bisa diatur sesuai kebutuhan tinggi minimum */
    }

    /* Judul produk */
    .info-card .product-title {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 6px;
        color: #181818;
        font-family: 'Inter', Arial, sans-serif;
    }

    /* Deskripsi */
    .info-card .desc {
        font-size: 16px;
        color: #6d6d6d;
        margin-bottom: 24px;
        font-weight: 500;
        line-height: 1.32;
        font-family: 'Inter', Arial, sans-serif;
    }

    /* Flex harga & stok di bawah */
    .info-card .info-bottom {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: auto;
        /* kunci: dorong info-bottom ke paling bawah card */
        gap: 8px;
        /* opsional, biar gak terlalu rapat */
    }

    /* Harga */
    .info-card .harga {
        font-weight: bold;
        font-size: 22px;
        margin-bottom: 0;
        color: #181818;
        font-family: 'Inter', Arial, sans-serif;
        line-height: 1;
    }

    /* Stok */
    .info-card .stok {
        font-size: 14px;
        color: #878d96;
        text-align: right;
        font-weight: 500;
        font-family: 'Inter', Arial, sans-serif;
        line-height: 1.2;
    }

    .order-form {
        background: #fff;
        margin: 0;
        border-radius: 0 0 18px 18px;
        box-shadow: 0 2px 10px #e1eaf7;
        padding: 18px 20px 22px 20px;
        display: flex;
        flex-direction: column;
        gap: 13px;
        border-top: 2px solid #e3e9ee;
        width: 100vw;
        box-sizing: border-box;
    }

    .order-form .judul {
        font-weight: 700;
        font-size: 15px;
        margin-bottom: 9px;
    }

    .form-row {
        display: flex;
        gap: 14px;
        align-items: center;
        margin-bottom: 0;
    }

    .form-row label {
        font-size: 16px;
        min-width: 60px;
        font-weight: 600;
    }

    .input-jumlah,
    .input-satuan {
        font-size: 16px;
        border: 1.2px solid #b2d8f7;
        background: #ffff;
        color: #444;
        border-radius: 7px;
        padding: 4px 10px;
        outline: none;
        width: 90px;
        transition: border 0.18s;
        height: 36px;
        box-sizing: border-box;
    }

    .input-satuan {
        font-size: 15px !important;
        height: 34px;
        width: 90px;
        padding: 3px 8px;
    }

    .input-satuan option {
        font-size: 10px !important;
    }

    .order-row {
        margin-bottom: 12px;
        align-items: center;
        gap: 6px;
    }

    .btn-plus,
    .btn-remove {
        border: none;
        border-radius: 7px;
        width: 38px;
        height: 36px;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.18s;
        box-shadow: 0 1px 3px #c8e4f7a8;
    }

    .btn-plus {
        background: #ffff;
        color: #00a830;
        margin-left: 5px;
    }

    .btn-plus:disabled {
        opacity: 0.4;
        cursor: default;
    }

    .btn-plus:hover:enabled {
        background: #00a830;
        color: #fff;

    }

    .btn-remove {
        background: #ffff;
        color: #a80000;
        margin-left: 5px;
        font-size: 22px;
    }

    .btn-remove:hover {
        background: #f66;
        color: #fff;
    }

    .input-jumlah:focus,
    .input-satuan:focus {
        border: 1.7px solid #CBCFD2;
        background: #e2f2ff;
    }

    .input-satuan {
        width: 120px;
        font-size: 19px;
    }

    @media (min-width: 1025px) {
        .header-detail {
            display: none !important;
        }

        /* Misal header kamu pakai .navbar atau .main-header, sesuaikan namanya */
        .main-header {
            position: fixed;
            width: 100vw;
            top: 0;
            left: 0;
            z-index: 100;
            height: 72px;
            /* contoh, sesuaikan dengan tinggi header asli */
        }

        /* Konten utama jangan ketiban header */
        .container-detail,
        .flex-main {
            padding-top: 42px;
            /* samakan dengan height header! */
        }

        .desktop-flexbox {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: flex-start;
            gap: 48px;
            width: 100vw;
            max-width: 1450px;
            margin: 0 auto;
            padding: 28px 0 40px 0;
            min-height: 80vh;
            background: #fff;
        }

        .desktop-col {
            background: #fff;
        }

        .desktop-img {
            flex: 1 1 50%;
            max-width: 600px;
            min-width: 340px;
            padding-left: 48px;
            padding-right: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }

        .desktop-info {
            flex: 1 1 50%;
            max-width: 750px;
            min-width: 340px;
            padding-right: 60px;
            padding-left: 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
        }

        .header-detail {
            width: 100%;
            min-width: 100%;
            margin-bottom: 18px;
            position: static;
            box-shadow: none;
            background: transparent;
            border: none;
            padding: 0 0 12px 0 !important;
        }

        .product-image-area {
            width: 100%;
            /* Rasio aspect 1:1 agar gambar selalu utuh (boleh diubah sesuai kebutuhan) */
            aspect-ratio: 1 / 1;
            background: #fff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            /* agar gambar tidak terpotong */
            display: block;
        }

        .info-card,
        .order-form {
            width: 100%;
            max-width: 620px;
            margin: 0 0 18px 0;
            border-radius: 12px;
            padding-left: 32px !important;
            padding-right: 32px !important;
            box-sizing: border-box;
        }

        .order-form .form-row {
            justify-content: center;
        }

        .form-row label,
        .input-jumlah,
        .input-satuan {
            font-size: 15px !important;
        }

        .input-satuan,
        .input-satuan option {
            font-size: 15px !important;
            min-height: 36px !important;
        }


        .btn-keranjang {
            width: 250px !important;
            min-width: 140px !important;
            max-width: 290px !important;
            font-size: 16px !important;
            border-radius: 7px;
            padding: 11px 0;
        }

        .footer-fixed {
            width: 100vw !important;
            background: #f8fbff;
            border-radius: 0 !important;
            box-shadow: none;
            padding: 28px 0 24px 0 !important;
            display: none !important;
            /* Hide footer in desktop */
        }

        .order-form .judul {
            font-size: 17px !important;
        }

        /* Tombol keranjang center dalam order-form di desktop */
        .order-btn-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 28px;
        }
    }

    /* Mobile (default) tetap center */
    .order-btn-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: 16px;
    }
</style>

<div class="flex-main">
    <div class="container-detail">

        <!-- HEADER -->
        <div class="header-detail">
            <a href="{{ asset('storage/logo/LogoKZ_transparant.png') }}">
                <svg width="32" height="32" fill="none" stroke="black" stroke-width="2.2">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </a>
            <div class="header-titles">
                <span class="main">Home</span>
            </div>
        </div>
        <div class="desktop-flexbox">
            <!-- Kolom Kiri: Gambar -->
            <div class="desktop-col desktop-img">
                <!-- IMAGE AREA -->
                <div class="product-image-area">
                    <!-- Jika tidak ada gambar, tampilkan placeholder -->
                    <div class="product-image-placeholder"><span>Product</span></div>
                    <!-- Jika ada gambar, tampilkan gambar dan sembunyikan placeholder dengan JS atau kondisi blade -->
                    <img src="{{ asset('storage/logo/LogoKZ_transparant.png') }}" alt="KZ Family" class="product-image">
                </div>

            </div>
            <!-- Kolom Kanan: Info & Form -->
            <div class="desktop-col desktop-info">

                <!-- INFO CARD -->
                <div class="info-card">
                    <div class="product-title">Nama Produk</div>
                    <div class="desc">Deskripsi Deskripsi Deskripsi Deskripsi Deskripsi Deskripsi Deskripsi Deskripsi Deskripsi Deskripsi Deskripsi</div>
                    <div class="info-bottom">
                        <div class="harga">Rp. 50.000</div>
                        <div class="stok">Produk Tersedia : 5 Slof 2 Bks</div>
                    </div>
                </div>

                <!-- ORDER FORM -->
                <form class="order-form" id="orderForm">
                    <div class="judul">Pesan Sekarang, Masukkan ke dalam keranjang !!!!</div>
                    <div id="order-rows">
                        <div class="form-row order-row">
                            <label for="jumlah-1">Jumlah</label>
                            <input type="number" min="1" id="jumlah-1" name="jumlah[]" class="input-jumlah" style="height:38px;" />
                            <select class="input-satuan" style="height:38px;" name="satuan[]">
                                <option value="satuan">Satuan</option>
                                <option value="bks">Bks</option>
                                <option value="slof">Slof</option>
                            </select>
                            <button type="button" class="btn-plus" onclick="tambahOrderRow(event)" title="Tambah baris">+</button>
                        </div>
                    </div>
                    <!-- Tombol keranjang center desktop & mobile -->
                    <div class="order-btn-wrapper">
                        <button type="submit" class="btn-keranjang">Masukkan Keranjang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

<script>
    let orderRowCount = 1;

    function tambahOrderRow(e) {
        orderRowCount++;
        // Disable tombol + di baris sebelumnya
        e.target.disabled = true;
        e.target.style.opacity = 0.4;

        // Buat row baru dengan tombol + dan tombol x
        let html = `
    <div class="form-row order-row">
        <label for="jumlah-${orderRowCount}">Jumlah</label>
        <input type="number" min="1" id="jumlah-${orderRowCount}" name="jumlah[]" class="input-jumlah" style="height:38px;" />
        <select class="input-satuan" style="height:38px;" name="satuan[]">
            <option value="satuan">Satuan</option>
            <option value="bks">Bks</option>
            <option value="slof">Slof</option>
        </select>
        <button type="button" class="btn-plus" onclick="tambahOrderRow(event)" title="Tambah baris">+</button>
        <button type="button" class="btn-remove" onclick="hapusOrderRow(this)" title="Hapus baris">x</button>
    </div>
    `;
        document.getElementById('order-rows').insertAdjacentHTML('beforeend', html);
        updateRemoveButtons();
    }

    function hapusOrderRow(btn) {
        // Hapus row ini
        let row = btn.closest('.order-row');
        let orderRows = document.querySelectorAll('#order-rows .order-row');
        // Jika tombol + pada baris yang dihapus dalam keadaan enabled, aktifkan tombol + di baris terakhir
        if (row.querySelector('.btn-plus:not(:disabled)')) {
            let lastPlus = orderRows[orderRows.length - 1].querySelector('.btn-plus');
            if (lastPlus) {
                lastPlus.disabled = false;
                lastPlus.style.opacity = 1;
            }
        }
        row.remove();
        updateRemoveButtons();
    }

    // Fungsi untuk update visibilitas tombol remove
    function updateRemoveButtons() {
        let rows = document.querySelectorAll('#order-rows .order-row');
        rows.forEach((row, i) => {
            let btnRemove = row.querySelector('.btn-remove');
            if (btnRemove) btnRemove.style.display = (rows.length > 1 && i > 0) ? 'flex' : 'none';
            let btnPlus = row.querySelector('.btn-plus');
            // Hanya tombol + di baris terakhir yang aktif
            if (btnPlus) {
                btnPlus.disabled = (i !== rows.length - 1);
                btnPlus.style.opacity = (i !== rows.length - 1) ? 0.4 : 1;
            }
        });
    }
</script>