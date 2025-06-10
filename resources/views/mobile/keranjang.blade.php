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

        .custom-check-item {
            width: 20px;
            height: 20px;
            appearance: none;
            -webkit-appearance: none;
            background-color: #fff;
            border: 2px solid #135291;
            border-radius: 5px;
            display: inline-block;
            position: relative;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            margin-left: -2px;
        }

        .custom-check-item:checked {
            background-color: #135291;
        }

        .custom-check-item:checked::after {
            content: "";
            position: absolute;
            top: 2px;
            left: 6px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }



        .custom-check-desktop {
            width: 24px;
            height: 24px;
            appearance: none;
            -webkit-appearance: none;
            background-color: #fff;
            border: 2px solid #135291;
            border-radius: 6px;
            display: inline-block;
            position: relative;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            margin-top: 2px;
            margin-left: 100px;
        }

        .custom-check-desktop:checked {
            background-color: #135291;
        }

        .custom-check-desktop:checked::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 7px;
            width: 6px;
            height: 12px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .cart-total-desktop {
            display: none;
        }

        .cart-badge-all-desktop {
            display: none;
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
            margin: 0 8px 12px 8px;
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
            .checkbox-margin-mobile {
                margin-left: 10px !important;
            }

            .no-padding-mobile {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .cart-full-mobile {
                width: 100% !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                border-radius: 0 !important;
            }

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
                margin-left: 20px;
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
                margin-right: 10px;

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
                margin-left: 25px;
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
                margin-right: 15px;
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
                margin-bottom: 80px !important;
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
                margin-left: 5px !important;
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

        @media (min-width: 1025px) {

            .cart-badge-all-desktop {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-top: 10px;
                margin-bottom: 0px;
                margin-left: 26px;
                user-select: none;

            }

            .cart-badge-all-desktop .cart-check {
                /* Copy persis dari .cart-check yang kamu pakai di dalam card */
                align-self: center;
                margin-top: 0px;
                margin-right: 0px;
                accent-color: #135291;
                width: 32px;
                height: 32px;
                flex-shrink: 0;
                border-radius: 8px;
                box-shadow: 0 1px 6px #13529144;
                border: none;
            }

            .cart-badge-label {
                font-family: 'Inter', Arial, sans-serif;
                font-size: 1.21rem;
                font-weight: 700;
                color: #18191a;
                letter-spacing: 0.2px;
            }

            .cart-footer-bar {
                display: none !important;
            }

            .cart-total-desktop {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                position: fixed;
                bottom: 36px;
                right: 38px;
                background: #fff;
                border-radius: 13px;
                box-shadow:
                    0 6px 48px 0 rgba(19, 82, 145, 0.13),
                    /* blur & opacity lebih besar, lebih “bold” */
                    0 3px 18px 0 rgba(19, 82, 145, 0.01),
                    0 1px 8px 0 #0002;
                /* shadow tambahan untuk efek dalam */

                padding: 32px 38px 24px 38px;
                z-index: 200;
                min-width: 420px;
                /* semula misal 270px jadi 420px */
                max-width: 520px;
                /* optional, biar ga kepanjangan */
            }

            .cart-total-title {
                font-size: 1.06rem;
                font-weight: bold;
                color: #1c2634;
                margin-bottom: 11px;
                letter-spacing: 0.5px;
            }

            .cart-total-row {
                width: 100%;
                display: flex;
                justify-content: space-between;
                font-size: 1.07rem;
                margin-bottom: 19px;
            }

            .cart-total-amount {
                color: #135291;
                font-weight: bold;
                font-size: 1.11rem;
                letter-spacing: 0.4px;
            }

            .cart-total-btn {
                width: 100%;
                padding: 11px 0;
                border: none;
                border-radius: 24px;
                font-weight: 700;
                font-size: 1rem;
                background: #135291;
                color: #fff;
                cursor: pointer;
                box-shadow: 0 3px 16px #13529133;
                transition: background 0.17s;
            }

            .cart-total-btn:hover {
                background: #103c66;
            }



        }


        @media (min-width: 601px) and (max-width: 1024px) {


            .main-content {
                padding-top: 0;
                margin-top: 0;
            }

            .cart-header {
                padding: 17px 20px 9px 20px;
                display: flex !important;
                margin-top: 12px;
            }

            .cart-header .cart-title {
                font-size: 1.13rem;
            }

            .cart-header .cart-subtitle {
                font-size: 1.01rem;
            }

            .cart-header .icon-profile {
                width: 42px;
                height: 42px;
                font-size: 2.1rem;
            }

            .cart-card {
                gap: 18px;
                padding: 18px 14px;
                margin: 16px 12px;
                border-radius: 12px;
                min-height: 108px;
            }

            .cart-img {
                width: 86px;
                height: 86px;
            }

            .cart-check {
                width: 28px;
                height: 28px;
                margin-left: 18px;
            }

            .cart-product-title {
                font-size: 1.18rem;
            }

            .cart-product-price {
                font-size: 1rem;
            }

            .cart-label {
                font-size: 1.01rem;
                margin-bottom: 5px;
            }

            .cart-row {
                gap: 10px;
            }

            .cart-input {
                width: 86px;
                height: 30px;
                font-size: 1.04rem;
                border-radius: 8px;
                padding: 2px 10px;
            }

            .cart-select-wrap {
                width: 86px;
                min-width: 66px;
                margin-right: 8px;
            }

            .cart-select {
                height: 30px;
                font-size: 1.04rem;
                border-radius: 8px;
                padding: 2px 26px 2px 10px;
            }

            .dropdown-icon {
                right: 8px;
                height: 19px;
            }

            .cart-footer-bar {
                padding: 8px 8px;
                gap: 8px;
                margin-bottom: 68px;
                height: auto;
                display: flex !important;
                /* PENTING: pastikan tetap tampil */
            }

            .footer-checkbox-custom input[type="checkbox"] {
                width: 28px;
                height: 28px;
            }

            .footer-checkbox-box {
                width: 28px;
                height: 28px;
                border-radius: 7px;
                border-width: 2px;
                margin-left: 0;
            }

            .footer-checkbox-box::after {
                left: 7px;
                top: 4px;
                width: 8px;
                height: 12px;
                border-width: 0 2.2px 2.2px 0;
            }

            .footer-checkbox-custom {
                margin-right: 10px;
            }

            .footer-label {
                font-size: 1.01rem;
            }

            .footer-total-group {
                min-width: 70px;
                margin-right: 10px;
            }

            .footer-total-title {
                font-size: 0.89rem;
            }

            .footer-total-amount {
                font-size: 1.07rem;
            }

            .cart-btn-checkout {
                font-size: 0.98rem;
                min-width: 108px;
                min-height: 36px;
                border-radius: 12px;
                padding: 7px 18px;
                margin-right: 20px;
            }

            /* Desktop elements tetap di-hide */
            .cart-badge-all-desktop,
            .cart-total-desktop {
                display: none !important;
            }
        }

        /* Selalu tampilkan footer-bar & hide desktop-total di tablet, baik portrait maupun landscape */
        @media (max-width: 1024px) {
            .cart-footer-bar {
                display: flex !important;
            }

            .cart-total-desktop {
                display: none !important;
            }

            .cart-badge-all-desktop {
                display: none !important;
            }
        }

        /* Untuk iPad Pro yang lebih lebar (landscape bisa sampai 1366px), masih mode mobile/tablet */
        @media (min-width: 1025px) and (max-width: 1366px) and (pointer: coarse) {
            .cart-footer-bar {
                display: flex !important;
            }

            .cart-total-desktop {
                display: none !important;
            }

            .cart-badge-all-desktop {
                display: none !important;
            }
        }
    </style>
    @endpush

    @section('content')
    <!-- Header -->
    <div class="cart-header">
        <div class="d-flex align-items-center">
            <img src="{{ asset('storage/logo/LogoKZ_transparant.png') }}" alt="Logo KZ" style="width:70px; height:auto; margin-right: 12px;">
            <div class="d-flex flex-column justify-content-center" style="height: 70px;">
                <div class="fw-bold mb-1">Toko KZ Family</div>
                <div class="text-muted" style="font-size:0.95rem;">Keranjang Saya</div>
            </div>
        </div>
        <div class="icon-profile">
            <svg fill="none" stroke="#222" stroke-width="2.1" viewBox="0 0 24 24" width="25" height="25">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </div>




    <!-- Daftar Cart Produk -->
    <div class="container-fluid px-md-4 px-0">
        <div class="row">
            <!-- Kolom KIRI: Daftar Produk -->
            <div class="col-12 col-lg-8 col-xl-8">

                <div class="cart-badge-all-desktop d-flex align-items-center ms-2 mb-3">
                    <div class="form-check d-flex align-items-center" style="position: relative;">
                        <input class="form-check-input select-all custom-check-desktop me-2" type="checkbox" id="selectAllDesktop" checked>
                        <label class="form-check-label fw-bold" for="selectAllDesktop" style="font-size: 1.1rem;">
                            Pilih Semua
                        </label>
                    </div>
                </div>

                <div class="cart-list">
                    @if($keranjangs->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-cart-x-fill fs-1 mb-3 d-block"></i>
                        <h5>Keranjang kamu masih kosong</h5>
                        <p class="small">Yuk, tambahkan produk favoritmu ke keranjang!</p>
                    </div>
                    @else
                    @foreach($keranjangs as $item)
                    <div class="card border-0 rounded-3 mb-3 px-3 py-2 position-relative w-100 cart-full-mobile"
                        style="box-shadow: 0 6px 12px rgba(0,0,0,0.2);">
                        {{-- Checkbox + Gambar + Info --}}
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div style="min-width: 28px;">
                                <input type="checkbox"
                                    class="custom-check-item item-checkbox checkbox-margin-mobile"
                                    data-id="{{ $item->id }}"
                                    checked>
                            </div>

                            <div class="border rounded-3 overflow-hidden shadow-sm"
                                style="width: 64px; height: 64px;">
                                <img src="{{ asset('storage/gambar_produk/' . $item->produk->gambar) }}"
                                    alt="{{ $item->produk->nama_produk }}"
                                    class="img-fluid w-100 h-100 object-fit-cover">
                            </div>

                            <div class="flex-grow-1">
                                <div class="fw-bold fs-6 text-dark mb-1">{{ $item->produk->nama_produk }}</div>
                                <div class="fw-semibold text-dark mb-1">Rp {{ number_format($item->produk->hargaProduks->first()->harga, 0, ',', '.') }}</div>
                                <div class="text-muted fw-semibold small mb-1">Jumlah :</div>

                                <div class="d-flex align-items-center gap-2 ms-auto flex-wrap justify-content-end">
                                    @foreach($item->produk->satuans()->orderByDesc('konversi_ke_satuan_utama')->get() as $satuan)
                                    @php
                                    $harga = $item->produk->hargaProduks->firstWhere('satuan_id', $satuan->id)?->harga ?? 0;
                                    @endphp
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="text-lowercase" style="font-size: 0.84rem; min-width: 28px;">{{ strtolower($satuan->nama_satuan) }}</span>
                                        <input type="number"
                                            class="form-control form-control-sm jumlah-per-satuan"
                                            name="jumlah_json[{{ $satuan->id }}]"
                                            data-harga="{{ $harga }}"
                                            data-id="{{ $item->id }}"
                                            data-satuan="{{ $satuan->id }}"
                                            min="0"
                                            value="{{ $item->jumlah_json[$satuan->id] ?? 0 }}"
                                            style="width: 56px; font-size: 0.85rem; padding: 2px 6px;">
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Tombol Delete -->
                            <form action="{{ route('mobile.keranjang.destroy', $item->id) }}" method="POST"
                                class="position-absolute top-0 end-0 m-2 d-flex align-items-center justify-content-center"
                                style="z-index: 10;">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="btn btn-sm btn-outline-danger rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px;">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

            </div>

            <!-- Kolom KANAN: Total Checkout (Desktop Only) -->
            <div class="col-lg-4 col-xl-4 d-none d-lg-block mt-4"> <!-- Tambah mt-4 di sini -->
                <div class="bg-white py-4 px-4"
                    style="box-shadow:
        0 12px 42px rgba(0, 0, 0, 0.08),
        0 3px 10px rgba(0, 0, 0, 0.04);
        border-radius: 18px;">

                    <!-- Judul -->
                    <div class="fw-semibold text-dark mb-3 fs-5 text-start w-100">
                        Total Keranjang
                    </div>

                    <!-- Baris Total -->
                    <div class="d-flex justify-content-between align-items-center mb-4 w-100">
                        <span class="text-secondary fs-6">Total</span>
                        <span class="fw-bold fs-5" id="totalKeranjangDesktop" style="color: #135291;">Rp 0</span>
                    </div>

                    <!-- Tombol Checkout -->
                    <button class="btn w-100 fw-bold text-white"
                        style="background-color: #135291; font-size: 1rem; padding: 12px 0; border-radius: 12px;">

                        CHECKOUT !!!
                    </button>
                </div>
            </div>
            <!-- Footer Mobile -->
            <div class="cart-footer-bar">
                <div class="footer-left">
                    <label class="footer-checkbox-custom">
                        <input type="checkbox" class="select-all" checked>
                        <span class="footer-checkbox-box"></span>
                    </label>
                    <span class="footer-label">Pilih Semua</span>
                </div>
                <div class="footer-total-group">
                    <span class="footer-total-title">Total</span>
                    <span class="footer-total-amount" id="totalKeranjang">Rp 0</span>
                </div>
                <button class="cart-btn-checkout">Checkout !!!</button>
            </div>


        </div>
    </div>
    </div>
    </div>






    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function formatRupiah(angka) {
                return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function hitungTotal() {
                let total = 0;
                document.querySelectorAll('.card').forEach(card => {
                    const checkbox = card.querySelector('.item-checkbox');
                    if (checkbox && checkbox.checked) {
                        const inputs = card.querySelectorAll('.jumlah-per-satuan');
                        inputs.forEach(input => {
                            const jumlah = parseFloat(input.value) || 0;
                            const harga = parseFloat(input.dataset.harga) || 0;
                            total += jumlah * harga;
                        });
                    }
                });

                document.getElementById('totalKeranjang').textContent = formatRupiah(total);
                document.getElementById('totalKeranjangDesktop').textContent = formatRupiah(total);
            }

            hitungTotal();

            document.querySelectorAll('.jumlah-per-satuan').forEach(input => {
                input.addEventListener('input', function() {
                    hitungTotal();

                    const card = input.closest('.card');
                    const keranjangId = input.dataset.id;
                    if (!keranjangId) return;

                    const inputs = card.querySelectorAll('.jumlah-per-satuan');
                    const jumlahJson = {};
                    inputs.forEach(i => {
                        const satuanId = i.dataset.satuan || i.name.match(/\[(\d+)\]/)?.[1];
                        const qty = parseFloat(i.value);
                        if (satuanId && !isNaN(qty) && qty > 0) {
                            jumlahJson[satuanId] = qty;
                        }
                    });

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const baseUrl = "{{ url('/pelanggan-area/keranjang') }}";

                    fetch(`${baseUrl}/${keranjangId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                jumlah_json: jumlahJson
                            })
                        })
                        .then(async res => {
                            const data = await res.json();
                            if (!res.ok || !data.success) {
                                console.error('RESPON STATUS:', res.status);
                                alert(data.message || 'Update gagal.');
                            } else {
                                console.log('Update berhasil');
                            }
                        })
                        .catch(err => {
                            console.error('Update error:', err);
                            alert('Terjadi kesalahan saat update jumlah.');
                        });
                });
            });

            document.querySelectorAll('.item-checkbox').forEach(cb => {
                cb.addEventListener('change', hitungTotal);
            });

            document.querySelectorAll('.select-all').forEach(master => {
                master.addEventListener('change', function() {
                    const checked = this.checked;
                    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = checked);
                    document.querySelectorAll('.select-all').forEach(sa => sa.checked = checked);
                    hitungTotal();
                });
            });

            document.querySelectorAll('form[action*="keranjang.destroy"]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Yakin ingin menghapus item ini dari keranjang?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>



    @endsection