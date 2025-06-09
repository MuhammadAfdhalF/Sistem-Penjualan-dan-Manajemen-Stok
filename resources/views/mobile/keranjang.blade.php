    @extends('layouts.template_mobile')
    @section('title', 'Keranjang Belanja - KZ Family')

    @push('head')
    <style>
        body {
            background: #fff;
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .cart-header {
            padding: 20px 18px 8px 18px;
            background: #fff;
            border-bottom: 2px solid #ededed;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .cart-header .cart-user {
            font-size: 1.13rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .cart-header .cart-title {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }

        .cart-header .cart-subtitle {
            font-size: 0.91rem;
            color: #6b6b6b;
            margin-top: 2px;
        }

        .cart-header .icon-profile {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #f2f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
        }

        /* Cart List */
        .cart-list {
            padding: 0 0 80px 0;
            background: #fafbff;
            min-height: 72vh;
        }

        .cart-card {
            display: flex;
            align-items: center;
            /* Ubah dari flex-start → center */
            gap: 20px;
            padding: 22px 18px;
            border-radius: 12px;
            border: none;
            background: #fff;
            margin: 18px 8px 12px 8px;
            box-shadow:
                0 -2px 24px 0 rgba(80, 110, 160, 0.14),
                /* shadow atas (lebih tebal) */
                0 8px 32px 0 rgba(100, 130, 180, 0.14),
                0 2px 8px 0 rgba(0, 0, 0, 0.10);
            position: relative;
            min-height: 120px;
        }

        .cart-check {
            align-self: center;
            /* Pastikan checkbox tetap tengah jika card tinggi */

            margin-top: 0px;
            margin-right: 8px;
            accent-color: #135291;
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            border-radius: 8px;
            box-shadow: 0 1px 6px #13529144;
        }

        .cart-img {
            width: 100px;
            height: 100px;
            background: #f4f5fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid #d2e3fa;
            box-shadow: 0 3px 16px #c1d8f799;
        }

        /* Product content right */
        .cart-card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            min-width: 0;
            position: relative;
        }

        /* Judul & harga */
        .cart-product-title {
            font-size: 1.28rem;
            font-weight: 800;
            color: #18191a;
            margin-bottom: 3px;
        }

        .cart-product-price {
            font-size: 1.07rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 15px;
        }

        /* Label jumlah di atas */
        .cart-label {
            font-size: 1.04rem;
            color: #929292;
            font-weight: 700;
            margin-bottom: 6px;
            display: block;
        }

        /* JUMLAH + SATUAN: flex kanan */
        .cart-row {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
            gap: 10px;
            margin-top: 0;
        }

        /* Input jumlah dan dropdown rata */
        .cart-input {
            width: 96px;
            height: 34px;
            font-size: 1rem;
            border: 1.5px solid #b8c3d6;
            border-radius: 8px;
            background: #fafdff;
            padding: 2px 8px;
            box-sizing: border-box;
            text-align: right;
            margin: 0;
            display: inline-block;
        }

        /* Custom select dengan icon */
        .cart-select-wrap {
            position: relative;
            display: inline-block;
            width: 96px;
            /* Cukup lebar untuk option dan icon */
            min-width: 70px;
            vertical-align: middle;
        }

        .cart-select {
            width: 100%;
            height: 34px;
            font-size: 1rem;
            border: 1.5px solid #b8c3d6;
            border-radius: 8px;
            background: #fafdff;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            box-sizing: border-box;
            outline: none;
            padding: 2px 32px 2px 10px;
            /* kanan cukup untuk icon, kiri normal */
        }

        .dropdown-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            display: flex;
            align-items: center;
            height: 22px;
        }

        .cart-footer-bar {
            width: 100vw;
            max-width: 100%;
            position: fixed;
            left: 0;
            bottom: 0;
            z-index: 99;
            background: #fff;
            box-shadow: 0 -2px 12px #0001;
            border-top: 1px solid #f1f1f1;
            display: flex;
            align-items: center;
            padding: 6px 7px;
            gap: 8px;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 5px;
            min-width: 90px;
        }

        .footer-checkbox-custom {
            display: flex;
            align-items: center;
            /* Vertikal tengah */
            justify-content: flex-start;
            /* Kiri */
            height: 100%;
            margin-left: 20px;
        }

        .footer-checkbox-custom input[type="checkbox"] {
            opacity: 0;
            width: 32px;
            height: 32px;
            margin: 0;
            position: absolute;
            left: 0;
            top: 0;
            cursor: pointer;
        }

        .footer-checkbox-box {
            display: inline-block;
            width: 32px;
            height: 32px;
            background: #fff;
            border-radius: 8px;
            border: 2.5px solid #b6c7d6;
            /* Border default */
            box-shadow: 0 1px 6px #13529133;
            position: relative;
            transition: background 0.15s, border 0.15s;
        }

        .footer-checkbox-custom input[type="checkbox"]:checked+.footer-checkbox-box {
            background: #135291;
            border: 2.5px solid #135291;
            /* Border biru jika dicentang */
        }

        .footer-checkbox-box::after {
            content: "";
            display: none;
            position: absolute;
            left: 8px;
            top: 5px;
            width: 10px;
            height: 16px;
            border: solid #fff;
            border-width: 0 4px 4px 0;
            border-radius: 2px;
            transform: rotate(45deg) scale(0.8);
        }

        .footer-checkbox-custom input[type="checkbox"]:checked+.footer-checkbox-box::after {
            display: block;
        }

        .footer-label {
            font-weight: bold;
            font-size: 0.97rem;
            color: #18191a;
            margin-left: 2px;
        }

        .footer-total-group {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            min-width: 80px;
            margin-left: auto;
            margin-right: 7px;
        }

        .footer-total-title {
            font-size: 0.83rem;
            color: #bcbcbc;
            font-weight: 700;
            margin-bottom: -1px;
            text-align: right;
            width: 100%;
        }

        .footer-total-amount {
            font-size: 1.03rem;
            font-weight: 800;
            color: #18191a;
            margin-top: -1px;
            letter-spacing: 0.3px;
            text-align: right;
            width: 100%;
        }

        .cart-btn-checkout {
            background: #135291;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 6px 15px;
            font-size: 1.01rem;
            font-weight: 700;
            min-width: 98px;
            min-height: 30px;
            box-shadow: 0 2px 8px #13529122;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 40px;
        }

        .cart-btn-checkout:active {
            background: #0c3e6a;
        }

        /* Responsive mobile */
        @media (max-width: 600px) {
            .cart-header {
                padding: 13px 7px 7px 12px;
                display: flex;
                margin-top: 10px;
            }

            .cart-card {
                gap: 8px;
                padding: 12px 2px;
                margin: 12px 2px;
                border-radius: 8px;
            }

            .cart-img {
                width: 64px;
                height: 64px;
            }

            .cart-check {
                width: 22px;
                height: 22px;
            }

            .cart-product-title {
                font-size: 1.03rem;
            }

            .cart-product-price {
                font-size: 0.91rem;
            }

            .cart-label {
                font-size: 0.93rem;
                margin-bottom: 4px;
            }

            .cart-row {
                gap: 7px;
            }

            .cart-input {
                width: 72px;
                height: 28px;
                font-size: 0.9rem;
                border-radius: 5px;
                padding: 1px 6px;
            }

            .cart-select-wrap {
                width: 72px;
                min-width: 60px;
            }

            .cart-select {
                height: 28px;
                font-size: 0.9rem;
                border-radius: 5px;
                padding: 1px 22px 1px 7px;
            }

            .dropdown-icon {
                right: 5px;
                height: 16px;
            }

            .cart-footer-bar {
                padding: 4px 2px;
                gap: 3px;
                margin-bottom: 100px;
            }

            .footer-checkbox-custom input[type="checkbox"] {
                width: 22px;
                height: 22px;
            }

            .footer-checkbox-box {
                width: 22px;
                height: 22px;
                border-radius: 5px;
                border-width: 2px;
                margin-left: 0px;
            }

            .footer-checkbox-box::after {
                left: 5px;
                top: 2.5px;
                width: 7px;
                height: 10px;
                border-width: 0 2.5px 2.5px 0;
            }

            .footer-checkbox-custom {
                margin-right: 5px;
            }

            .footer-label {
                font-size: 0.95rem;
            }

            .footer-total-group {
                min-width: 62px;
                margin-right: 5px;
            }

            .footer-total-title {
                font-size: 0.71rem;
            }

            .footer-total-amount {
                font-size: 0.89rem;
            }

            .cart-btn-checkout {
                font-size: 0.77rem;
                min-width: 90px;
                min-height: 32px;
                border-radius: 10px;
                padding: 5px 8px;
                margin-right: 10px;
            }
        }

        @media (min-width: 601px) {
            .cart-header {
                display: none !important;
            }

            body {
                margin-top: 10px;
            }

        }

        @media (pointer: coarse) and (orientation: landscape) {

            body {
                margin-top: 0 !important;
                padding: 0 !important;
            }

            .main-content {
                padding-top: 0;
                margin-top: 0;
            }

            .cart-header {
                padding: 13px 7px 7px 12px !important;
                display: flex !important;

            }

            .cart-header .cart-title {
                font-size: 1.03rem !important;
                font-weight: 600;
                color: #333;
            }

            .cart-header .cart-subtitle {
                font-size: 0.91rem !important;
            }

            .cart-header .icon-profile {
                width: 35px !important;
                height: 35px !important;
                font-size: 1.7rem !important;
            }

            .cart-card {
                gap: 8px !important;
                padding: 12px 2px !important;
                margin: 12px 2px !important;
                border-radius: 8px !important;
            }

            .cart-img {
                width: 64px !important;
                height: 64px !important;
            }

            .cart-check {
                width: 22px !important;
                height: 22px !important;
            }

            .cart-product-title {
                font-size: 1.03rem !important;
            }

            .cart-product-price {
                font-size: 0.91rem !important;
            }

            .cart-label {
                font-size: 0.93rem !important;
                margin-bottom: 4px !important;
            }

            .cart-row {
                gap: 7px !important;
            }

            .cart-input {
                width: 72px !important;
                height: 28px !important;
                font-size: 0.9rem !important;
                border-radius: 5px !important;
                padding: 1px 6px !important;
            }

            .cart-select-wrap {
                width: 72px !important;
                min-width: 60px !important;
            }

            .cart-select {
                height: 28px !important;
                font-size: 0.9rem !important;
                border-radius: 5px !important;
                padding: 1px 22px 1px 7px !important;
            }

            .dropdown-icon {
                right: 5px !important;
                height: 16px !important;
            }

            .cart-footer-bar {
                padding: 4px 2px !important;
                gap: 3px !important;
                margin-bottom: 50px !important;
                height: auto !important;
            }

            .footer-checkbox-custom input[type="checkbox"] {
                width: 22px !important;
                height: 22px !important;
            }

            .footer-checkbox-box {
                width: 22px !important;
                height: 22px !important;
                border-radius: 5px !important;
                border-width: 2px !important;
                margin-left: 0px !important;
            }

            .footer-checkbox-box::after {
                left: 5px !important;
                top: 2.5px !important;
                width: 7px !important;
                height: 10px !important;
                border-width: 0 2.5px 2.5px 0 !important;
            }

            .footer-checkbox-custom {
                margin-right: 5px !important;
            }

            .footer-label {
                font-size: 0.95rem !important;
            }

            .footer-total-group {
                min-width: 62px !important;
                margin-right: 5px !important;
            }

            .footer-total-title {
                font-size: 0.71rem !important;
            }

            .footer-total-amount {
                font-size: 0.89rem !important;
            }

            .cart-btn-checkout {
                font-size: 0.77rem !important;
                min-width: 90px !important;
                min-height: 32px !important;
                border-radius: 10px !important;
                padding: 5px 8px !important;
                margin-right: 10px !important;
            }
        }
    </style>
    @endpush
    @section('content')
    <!-- Header -->
    <div class="cart-header">
        <div>
            <div class="cart-title">Toko KZ Family</div>
            <div class="cart-subtitle">Keranjang Belanja</div>
        </div>
        <div class="icon-profile">
            <svg fill="none" stroke="#222" stroke-width="2.1" viewBox="0 0 24 24" width="25" height="25">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </div>

    <!-- Daftar Cart (STATIC / 1 ITEM SAJA) -->
    <div class="cart-list">
        <div class="cart-card">
            <input type="checkbox" class="cart-check" checked>
            <div class="cart-img">
                <!-- Gambar produk -->
                <img src="{{ asset('storage/gambar_produk/contoh_produk.jpg') }}" alt="Product">
            </div>
            <div class="cart-card-content">
                <div class="cart-product-title">Kopi Gula Aren 1L</div>
                <div class="cart-product-price">Rp. 27.000</div>
                <span class="cart-label">Jumlah :</span>
                <div class="cart-row">
                    <input type="number" min="1" class="cart-input" value="1">
                    <div class="cart-select-wrap">
                        <select class="cart-select">
                            <option>Botol</option>
                            <option>Karton</option>
                        </select>
                        <span class="dropdown-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24">
                                <path d="M7 10l5 5 5-5" stroke="#222" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="cart-footer-bar">
        <div class="footer-left">
            <label class="footer-checkbox-custom">
                <input type="checkbox" checked>
                <span class="footer-checkbox-box"></span>
            </label>
            <span class="footer-label">Semua</span>
        </div>
        <div class="footer-total-group">
            <span class="footer-total-title">Total</span>
            <span class="footer-total-amount">Rp. 50.000</span>
        </div>
        <button class="cart-btn-checkout">Checkout !!!</button>
    </div>



    @endsection