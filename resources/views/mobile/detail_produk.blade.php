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

    #toast-keranjang {
        position: fixed;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.63);
        border: 1px solid rgba(0, 0, 0, 0.3);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.3s;
        pointer-events: none;
    }

    .toast-modal {
        background: rgba(255, 255, 255, 0.6);
        padding: 24px 32px 16px 32px;
        border-radius: 24px;
        text-align: center;
        min-width: 210px;
        max-width: 88vw;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.10);
        display: flex;
        flex-direction: column;
        align-items: center;
        pointer-events: auto;
    }

    /* Responsive icon & font */
    .toast-icon svg {
        width: 56px;
        height: 56px;
    }

    .toast-msg span {
        color: #135291;
        font-weight: bold;
        font-size: 1.10rem;
        font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        letter-spacing: 0.01em;
    }

    /* Tablet */
    @media (max-width: 700px) {
        .toast-modal {
            padding: 18px 18px 11px 18px;
            border-radius: 19px;
            min-width: 160px;
            max-width: 96vw;
        }

        .toast-icon svg {
            width: 38px;
            height: 38px;
        }

        .toast-msg span {
            font-size: 1rem;
        }
    }

    /* Smartphone */
    @media (max-width: 420px) {
        .toast-modal {
            padding: 10px 8vw 7px 8vw;
            border-radius: 12px;
            min-width: 120px;
            max-width: 98vw;
        }

        .toast-icon svg {
            width: 24px;
            height: 24px;
        }

        .toast-msg span {
            font-size: 0.98rem;
        }
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
        background: #135291;
        color: #ffff;
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
        background: #135291;
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
        background: #fff;
        /* Shadow biru tua lembut di bawah */
        box-shadow: 0 4px 18px -2px #13529133, 0 1px 0 #13529130;
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
        stroke: rgb(0, 0, 0);
        /* Biru tua */
        /* atau kalau ingin biru muda: stroke: #C1E5FF; */
    }


    .header-titles {
        color: rgb(0, 0, 0);
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




    @media (max-width: 600px) {
        .product-image-area {
            aspect-ratio: 1 / 1;
            max-width: 100vw;
            margin-top: 24px;
            /* tambahkan margin top khusus mobile */
        }

        .product-image {
            width: 80%;
            height: 80%;
            object-fit: contain;
            display: block;
            margin: auto;
        }

        .footer-mobile-nav {
            display: none !important;
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
        font-size: 17px;
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
            padding-top: 0px;
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
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 450px;
            /* pastikan kolom kiri tinggi */
        }

        .desktop-info {

            flex: 1 1 50%;
            max-width: 750px;
            min-width: 340px;
            padding-right: 60px;
            padding-left: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            /* UBAH dari flex-start ke center */
            height: 100%;

            /* UBAH dari flex-start ke center */
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
            height: 100%;
            min-height: 350px;
            /* agar area gambar tidak terlalu kecil */
            min-width: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
        }

        .product-image {
            width: 90%;
            height: 90%;
            max-width: 420px;
            /* batas maksimal gambar */
            max-height: 420px;
            object-fit: contain;
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

    @media (max-width: 1000px) and (orientation: landscape) {
        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .footer-mobile-nav {
            display: none !important;
        }

        .product-image-area {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            min-height: 200px;
            /* atau sesuaikan */
            background: #fff;
            width: 100vw;
            margin: 0 auto;
        }

        .product-image {
            display: block;
            margin: 0 auto;
            width: 70vw;
            /* responsive, bisa diubah ke max-width: 300px; */
            max-width: 300px;
            height: auto;
            object-fit: contain;
        }
    }

    /* ======================= */
    /*  RESPONSIVE TABLET CSS  */
    /* ======================= */

    @media (min-width: 600px) and (max-width: 1024px) {

        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .desktop-flexbox {
            flex-direction: column !important;
            gap: 22px !important;
            padding: 16px 0 20px 0 !important;
            max-width: 96vw !important;
            min-width: 0 !important;
            width: 98vw !important;
            box-sizing: border-box;
        }

        .desktop-img,
        .desktop-col {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box;
            padding: 0 !important;
        }

        .product-image-area {
            max-width: 350px !important;
            min-width: 170px !important;
            min-height: 220px !important;
            margin: 0 auto 8px auto !important;
            border-radius: 15px !important;
        }

        .product-image {
            max-width: 100% !important;
            max-height: 200px !important;
            margin: auto !important;
            display: block !important;
        }

        .desktop-info {
            padding: 0 8vw !important;
            max-width: 100vw !important;
            min-width: 0 !important;
        }

        .info-card,
        .order-form {
            width: 100% !important;
            max-width: 99vw !important;
            min-width: 0 !important;
            border-radius: 14px !important;
            padding-left: 16px !important;
            padding-right: 16px !important;
            margin: 0 0 14px 0 !important;
            box-sizing: border-box !important;
        }

        .order-form {
            padding-bottom: 18px !important;
            margin-bottom: 18px !important;
        }

        .order-btn-wrapper {
            margin-top: 18px !important;
            margin-bottom: 6px !important;
        }

        .footer-fixed {
            padding: 12px 0 !important;
            border-radius: 0 0 12px 12px !important;
            font-size: 17px !important;
        }

        .header-detail {
            font-size: 20px !important;
            padding: 18px 8vw 8px 8vw !important;
            min-width: 0 !important;
        }
    }
</style>

<div class="flex-main">
    <div class="container-detail">

        <!-- HEADER -->
        <div class="header-detail">
            <a href="javascript:history.back()">
                <svg width="32" height="32" fill="none" stroke="#135291" stroke-width="2.2">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </a>
            <div class="header-titles">
                <span class="main">Detail Produk</span>
            </div>
        </div>
        <div class="desktop-flexbox shadow mt-2">
            <!-- Kolom Kiri: Gambar -->
            <div class="desktop-col desktop-img">
                <div class="product-image-area">
                    @if($produk->gambar)
                    <img src="{{ asset('storage/gambar_produk/' . $produk->gambar) }}" alt="{{ $produk->nama_produk }}" class="product-image">
                    @else
                    <div class="product-image-placeholder"><span>No Image</span></div>
                    @endif
                </div>
            </div>
            <!-- Kolom Kanan: Info & Form -->
            <div class="desktop-col desktop-info">
                <!-- INFO CARD -->
                <div class="info-card">
                    <div class="product-title">{{ $produk->nama_produk }}</div>
                    <div class="desc">{{ $produk->deskripsi }}</div>
                    <div class="info-bottom">
                        <div class="harga">
                            @foreach($produk->satuans->take(3) as $satuan)
                            @php
                            $harga = $produk->hargaProduks->where('satuan_id', $satuan->id)->first();
                            @endphp
                            @if($harga)
                            Rp. {{ number_format($harga->harga, 0, ',', '.') }}/{{ $satuan->nama_satuan }}<br>
                            @endif
                            @endforeach
                        </div>


                        <div class="stok">
                            Produk Tersedia : {{ $produk->stok_bertingkat }}
                        </div>
                    </div>
                </div>


                <form id="orderForm" method="POST" action="{{ route('mobile.keranjang.store') }}">
                    @csrf
                    <input type="hidden" name="produk_id[]" value="{{ $produk->id }}">

                    <div class="order-form" id="order-rows">
                        <div class="form-row order-row">
                            <label>Jumlah</label>
                            <input type="number" min="1" class="input-jumlah" style="height:38px;" />
                            <select class="input-satuan" style="height:38px;">
                                @foreach($produk->satuans as $satuan)
                                <option value="{{ $satuan->id }}">{{ $satuan->nama_satuan }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-plus" onclick="tambahOrderRow(event)">+</button>
                            <button type="button" class="btn-remove" style="display:none;" onclick="hapusOrderRow(this)">x</button>
                        </div>
                    </div>
                    <!-- DI SINI! Input hidden, harus ADA DI DALAM FORM -->
                    <input type="hidden" name="jumlah_json[]" class="jumlah-json">
                    <div class="order-btn-wrapper">
                        <button type="submit" class="btn-keranjang">Masukkan Keranjang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="toast-keranjang" style="display:none;">
        <div class="toast-modal">
            <div class="toast-icon">
                <svg width="56" height="56" viewBox="0 0 56 56">
                    <circle cx="28" cy="28" r="22" fill="#135291" />
                    <path d="M19 29l6 6 12-12" stroke="rgba(255, 255, 255, 0.65)"
                        stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="toast-msg">
                <span id="toast-msg-text">Produk dimasukkan ke keranjang anda</span>
            </div>
        </div>
    </div>




</div>

@endsection





<script>
    function updateRemoveButtons() {
        let rows = document.querySelectorAll('#order-rows .order-row');
        rows.forEach((row, i) => {
            let btnRemove = row.querySelector('.btn-remove');
            let btnPlus = row.querySelector('.btn-plus');
            if (btnRemove) btnRemove.style.display = (rows.length > 1) ? 'inline-block' : 'none';
            if (btnPlus) {
                btnPlus.disabled = (i !== rows.length - 1);
                btnPlus.style.opacity = (i !== rows.length - 1) ? 0.4 : 1;
            }
        });
    }

    function tambahOrderRow(e) {
        let satuanOptions = document.querySelector('.input-satuan').innerHTML;
        let html = `
        <div class="form-row order-row">
            <label>Jumlah</label>
            <input type="number" min="1" class="input-jumlah" style="height:38px;" />
            <select class="input-satuan" style="height:38px;">${satuanOptions}</select>
            <button type="button" class="btn-plus" onclick="tambahOrderRow(event)">+</button>
            <button type="button" class="btn-remove" onclick="hapusOrderRow(this)">x</button>
        </div>
        `;
        document.getElementById('order-rows').insertAdjacentHTML('beforeend', html);
        updateRemoveButtons();
        if (e && e.target) {
            e.target.disabled = true;
            e.target.style.opacity = 0.4;
        }
    }

    function hapusOrderRow(btn) {
        btn.closest('.order-row').remove();
        updateRemoveButtons();
    }

    // Toast show function
    function showToast(msg) {
        let toast = document.getElementById('toast-keranjang');
        let msgText = document.getElementById('toast-msg-text');
        msgText.textContent = msg || 'Produk dimasukkan ke keranjang anda';
        toast.style.display = 'flex';
        toast.style.opacity = 1;

        setTimeout(() => {
            toast.style.opacity = 0;
            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
        }, 500);
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateRemoveButtons();

        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Build jumlah JSON object
            let jumlahObj = {};
            document.querySelectorAll('#order-rows .order-row').forEach(function(row) {
                let satuanId = row.querySelector('.input-satuan').value;
                let qty = parseFloat(row.querySelector('.input-jumlah').value) || 0;
                if (qty > 0 && satuanId) {
                    if (jumlahObj[satuanId]) {
                        jumlahObj[satuanId] += qty;
                    } else {
                        jumlahObj[satuanId] = qty;
                    }
                }
            });
            let hiddenInput = document.querySelector('input.jumlah-json[name="jumlah_json[]"]');
            hiddenInput.value = JSON.stringify(jumlahObj);

            if (Object.keys(jumlahObj).length === 0) {
                alert('Isi minimal 1 jumlah & satuan!');
                return;
            }

            // Buat data form
            let formData = new FormData(e.target);

            fetch(e.target.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Produk dimasukkan ke keranjang anda');
                        // (Opsional) reset input jumlah di form
                        document.querySelectorAll('.input-jumlah').forEach(input => input.value = '');
                    } else {
                        alert(data.message || 'Gagal menambahkan ke keranjang!');
                    }
                })
                .catch(() => {
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                });
        });
    });
</script>