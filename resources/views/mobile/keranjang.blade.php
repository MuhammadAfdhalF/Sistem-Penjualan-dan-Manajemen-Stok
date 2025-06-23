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

        /* Hilangkan spinner browser default */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Input counter style clean */
        .input-counter input.form-control {
            border: none;
            outline: none;
            box-shadow: none;
            padding: 0;
            font-weight: 600;
        }

        .input-counter .btn {
            font-weight: bold;
            line-height: 1;
            height: 32px;
            width: 28px;
            font-size: 1rem;
            border-radius: 0;
            display: flex;
            justify-content: center;
            align-items: center;
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

            .input-counter {
                margin-right: 10px;
            }

            .input-counter input.form-control {
                height: 26px;
                width: 15px;
                font-size: 0.4rem;
            }

            .input-counter .btn {
                width: 10px;
                height: 26px;
                font-size: 0.8rem;
                padding: 0;
            }

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

        @media (max-width: 991.98px) {
            .col-12.col-lg-8.col-xl-8 {
                margin-bottom: 5rem !important;
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
    <!-- Header -->
    <div class="cart-header">
        <div class="d-flex align-items-center">
            <img src="{{ asset('storage/logo/LogoKZ_transparant.png') }}" alt="Logo KZ" style="width:70px; height:auto; margin-right: 12px;">
            <div class="d-flex flex-column justify-content-center" style="height: 70px;">
                <div class="fw-bold mb-1">Toko KZ Family</div>
                <div class="text-muted" style="font-size:0.95rem;">Keranjang Saya</div>
            </div>
        </div>
        <a href="{{ route('mobile.keranjang.index') }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-cart text-dark"></i> <!-- Ini bikin ikon jadi hitam -->
        </a>


    </div>




    <!-- Daftar Cart Produk -->
    <div class="container-fluid px-md-4 px-0" style="max-width: 1280px;">
        <div class="row">
            <!-- Kolom KIRI: Daftar Produk -->
            <div class="col-12 col-lg-8 col-xl-8">

                <div class="cart-badge-all-desktop d-flex align-items-center ms-2 mb-3">
                    <div class="form-check d-flex align-items-center" style="position: relative;">
                        <input class="form-check-input select-all custom-check-desktop me-2" type="checkbox" id="selectAllDesktop">
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
                    <div class="card border-1 rounded-3 mb-2 px-3 py-2 position-relative w-100 cart-full-mobile"
                        style="box-shadow: 0 3px 6px rgba(0,0,0,0.2);">
                        {{-- Checkbox + Gambar + Info --}}
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div style="min-width: 28px;">
                                <input type="checkbox"
                                    class="custom-check-item item-checkbox checkbox-margin-mobile"
                                    data-id="{{ $item->id }}">
                            </div>

                            <div class="border rounded-3 overflow-hidden shadow-sm"
                                style="width: 64px; height: 64px;">
                                <img src="{{ asset('storage/gambar_produk/' . $item->produk->gambar) }}"
                                    alt="{{ $item->produk->nama_produk }}"
                                    class="img-fluid w-100 h-100 object-fit-cover">
                            </div>

                            <div class="flex-grow-1">
                                <div class="fw-bold fs-6 text-dark mb-1">{{ $item->produk->nama_produk }}</div>
                                <div class="text-muted small mb-1">
                                    @php
                                    // Kumpulkan semua harga satuan untuk tampilan di keranjang
                                    $hargaList = $item->produk->satuans->map(function($satuan) use ($item) {
                                    $hargaObj = $item->produk->hargaProduks->firstWhere('satuan_id', $satuan->id);
                                    if ($hargaObj) {
                                    return 'Rp ' . number_format($hargaObj->harga, 0, ',', '.') . '/' . $satuan->nama_satuan;
                                    }
                                    return null;
                                    })->filter()->toArray();
                                    @endphp
                                    {!! implode('<br>', $hargaList) !!}
                                </div>

                                <div class="text-muted fw-semibold small mb-2">Jumlah :</div>
                                <div class="jumlah-satuan-wrapper text-end">
                                    {{-- Jika item keranjang punya data jumlah_json --}}
                                    @if(!empty($item->jumlah_json))
                                    {{-- Loop untuk setiap satuan yang sudah ada di keranjang --}}
                                    @foreach($item->jumlah_json as $satuanId => $jumlah)
                                    @php
                                    $currentSatuan = $item->produk->satuans->firstWhere('id', $satuanId);
                                    $hargaSatuan = $item->produk->hargaProduks->firstWhere('satuan_id', $satuanId);
                                    @endphp
                                    @if($currentSatuan && $hargaSatuan)
                                    <div class="row gx-2 align-items-center satuan-group mb-2 justify-content-end">
                                        <div class="col-auto">
                                            <div class="input-group input-group-sm" style="border: none;">
                                                <button class="btn btn-sm bg-light border-0 minus-btn" type="button" style="font-size: 0.75rem;">−</button>
                                                <input type="text" class="form-control text-center jumlah-input bg-light border-0" placeholder="0" value="{{ $jumlah }}"
                                                    data-keranjang-id="{{ $item->id }}" data-satuan-id="{{ $currentSatuan->id }}" data-harga-satuan="{{ $hargaSatuan->harga }}"
                                                    data-produk-id="{{ $item->produk->id }}" style="width: 45px; font-size: 0.75rem;">
                                                <button class="btn btn-sm bg-light border-0 plus-btn" type="button" style="font-size: 0.75rem;">+</button>
                                            </div>
                                        </div>
                                        <div class="col-auto" style="width: 80px;">
                                            <select class="form-select form-select-sm border-0 bg-light satuan-select" style="font-size: 0.75rem;">
                                                @foreach($item->produk->satuans as $satuan)
                                                @php
                                                $optHargaSatuan = $item->produk->hargaProduks->firstWhere('satuan_id', $satuan->id);
                                                @endphp
                                                <option value="{{ $satuan->id }}" data-harga="{{ $optHargaSatuan->harga ?? 0 }}" @if($satuan->id == $currentSatuan->id) selected @endif>{{ $satuan->nama_satuan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-auto" style="width: 40px;">
                                            <button type="button" class="btn btn-sm btn-light text-success tambah-jumlah w-100" style="font-size: 0.75rem;"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                        <div class="col-auto" style="width: 40px;">
                                            <button type="button" class="btn btn-sm btn-light text-danger hapus-jumlah w-100 {{ count($item->jumlah_json) == 1 ? 'd-none' : '' }}" style="font-size: 0.75rem;"><i class="bi bi-x-lg"></i></button>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                    @else
                                    {{-- Default satu baris jika keranjang_item.jumlah_json kosong --}}
                                    <div class="row gx-2 align-items-center satuan-group mb-2 justify-content-end">
                                        <div class="col-auto">
                                            <div class="input-group input-group-sm" style="border: none;">
                                                <button class="btn btn-sm bg-light border-0 minus-btn" type="button" style="font-size: 0.75rem;">−</button>
                                                <input type="text" class="form-control text-center jumlah-input bg-light border-0" placeholder="0" value="0"
                                                    data-keranjang-id="{{ $item->id }}" data-satuan-id="{{ $item->produk->satuans->first()->id ?? '' }}" data-harga-satuan="{{ $item->produk->hargaProduks->first()->harga ?? 0 }}"
                                                    data-produk-id="{{ $item->produk->id }}" style="width: 45px; font-size: 0.75rem;">
                                                <button class="btn btn-sm bg-light border-0 plus-btn" type="button" style="font-size: 0.75rem;">+</button>
                                            </div>
                                        </div>
                                        <div class="col-auto" style="width: 80px;">
                                            <select class="form-select form-select-sm border-0 bg-light satuan-select" style="font-size: 0.75rem;">
                                                @foreach($item->produk->satuans as $satuan)
                                                @php
                                                $optHargaSatuan = $item->produk->hargaProduks->firstWhere('satuan_id', $satuan->id);
                                                @endphp
                                                <option value="{{ $satuan->id }}" data-harga="{{ $optHargaSatuan->harga ?? 0 }}">{{ $satuan->nama_satuan }}</option>
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
                                    @endif
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

                    <!-- Tombol Checkout (desktop) -->
                    <form id="checkoutFormDesktop" method="GET" action="{{ route('mobile.proses_transaksi.index') }}">
                        <input type="hidden" name="keranjang_id[]" value="" disabled> {{-- Placeholder dummy --}}
                        <button type="submit" id="btnCheckoutDesktop" class="btn w-100 fw-bold text-white text-center"
                            style="background-color: #135291; font-size: 1rem; padding: 12px 0; border-radius: 12px; border: none;">
                            CHECKOUT !!!
                        </button>
                    </form>


                </div>
            </div>

            <!-- Footer Mobile -->
            <div class="cart-footer-bar">
                <div class="footer-left">
                    <label class="footer-checkbox-custom">
                        <input type="checkbox" class="select-all">
                        <span class="footer-checkbox-box"></span>
                    </label>
                    <span class="footer-label">Pilih Semua</span>
                </div>
                <div class="footer-total-group">
                    <span class="footer-total-title">Total</span>
                    <span class="footer-total-amount" id="totalKeranjang">Rp 0</span>
                </div>
                <form id="checkoutForm" method="GET" action="{{ route('mobile.proses_transaksi.index') }}">
                    <input type="hidden" name="keranjang_id[]" value="" disabled> {{-- Placeholder dummy --}}
                    <button type="submit" id="btnCheckoutDesktop"
                        class="btn fw-bold text-white text-center"
                        style="
        background-color: #135291;
        font-size: 0.8rem;
        width: 120px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: none;
        margin-right : 5px
    ">
                        CHECKOUT !!!
                    </button>

                </form>

            </div>
        </div>
    </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Fungsi utilitas untuk memformat Rupiah
            function formatRupiah(angka) {
                return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            // Fungsi untuk mengikat event pada grup satuan (input +, -, select, tambah, hapus)
            function bindSatuanGroupEvents(group) {
                const card = group.closest('.card');

                const jumlahInput = group.querySelector('.jumlah-input');
                if (jumlahInput) {
                    jumlahInput.addEventListener('input', function() {
                        updateItemAndTotal(card);
                    });
                }

                group.addEventListener('click', function(e) {
                    e.stopPropagation();

                    const input = group.querySelector('.jumlah-input');
                    if (!input) return;

                    let val = parseInt(input.value) || 0;
                    let changed = false;

                    if (e.target.closest('.minus-btn')) {
                        input.value = Math.max(0, val - 1);
                        changed = true;
                    } else if (e.target.closest('.plus-btn')) {
                        input.value = val + 1;
                        changed = true;
                    }

                    if (changed) {
                        input.dispatchEvent(new Event('input'));
                    }
                });

                const satuanSelect = group.querySelector('.satuan-select');
                if (satuanSelect) {
                    satuanSelect.addEventListener('change', function(e) {
                        e.stopPropagation();
                        const selectedOption = this.options[this.selectedIndex];
                        const harga = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
                        const associatedJumlahInput = this.closest('.satuan-group').querySelector('.jumlah-input');
                        if (associatedJumlahInput) {
                            associatedJumlahInput.dataset.hargaSatuan = harga;
                        }
                        updateItemAndTotal(card);
                    });
                }

                const tambahBtn = group.querySelector('.tambah-jumlah');
                const hapusBtn = group.querySelector('.hapus-jumlah');

                if (tambahBtn) {
                    tambahBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const wrapper = group.closest('.jumlah-satuan-wrapper');
                        const clone = group.cloneNode(true);

                        // Reset nilai input pada kloningan
                        clone.querySelector('.jumlah-input').value = '';
                        // Reset pilihan satuan ke opsi pertama (atau default)
                        clone.querySelector('.satuan-select').selectedIndex = 0;
                        // Pastikan data-harga-satuan di input yang dikloning juga direset ke harga default satuan pertama
                        const defaultHarga = parseFloat(clone.querySelector('.satuan-select option:first-child')?.getAttribute('data-harga')) || 0;
                        clone.querySelector('.jumlah-input').dataset.hargaSatuan = defaultHarga;

                        // Tampilkan tombol hapus untuk grup yang baru dikloning
                        clone.querySelector('.hapus-jumlah')?.classList.remove('d-none');

                        wrapper.appendChild(clone); // Tambahkan kloningan ke DOM
                        bindSatuanGroupEvents(clone); // Ikat event untuk elemen kloningan baru

                        // Pastikan tombol hapus untuk semua grup sudah terlihat jika ada lebih dari 1
                        wrapper.querySelectorAll('.hapus-jumlah').forEach(btn => btn.classList.remove('d-none'));

                        updateItemAndTotal(card);
                    });
                }

                if (hapusBtn) {
                    hapusBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const wrapper = group.closest('.jumlah-satuan-wrapper');
                        const allSatuanGroups = wrapper.querySelectorAll('.satuan-group');

                        if (allSatuanGroups.length > 1) {
                            group.remove(); // Hapus grup dari DOM
                            // Jika setelah dihapus hanya sisa satu grup, sembunyikan tombol hapusnya
                            if (wrapper.querySelectorAll('.satuan-group').length === 1) {
                                wrapper.querySelector('.hapus-jumlah')?.classList.add('d-none');
                            }
                        } else {
                            // Jika hanya satu grup tersisa, reset nilainya menjadi 0
                            const inputToReset = group.querySelector('.jumlah-input');
                            const selectToReset = group.querySelector('.satuan-select');
                            if (inputToReset) {
                                inputToReset.value = 0;
                                if (selectToReset) selectToReset.selectedIndex = 0; // Reset select ke opsi pertama
                            }
                            group.querySelector('.hapus-jumlah')?.classList.add('d-none'); // Selalu sembunyikan jika hanya 1
                        }
                        updateItemAndTotal(card);
                    });
                }
            }

            // Fungsi utama untuk memperbarui item keranjang di backend dan menghitung total
            function updateItemAndTotal(card) {
                const keranjangId = card.querySelector('.item-checkbox').dataset.id;
                if (!keranjangId) return;

                let totalHargaProduk = 0;
                const jumlahJson = {};
                let isAnyQuantityGreaterThanZero = false;

                card.querySelectorAll('.satuan-group').forEach(group => {
                    const jumlahInput = group.querySelector('.jumlah-input');
                    const satuanSelect = group.querySelector('.satuan-select');
                    const satuanId = satuanSelect.value;
                    const jumlah = parseInt(jumlahInput.value) || 0;

                    // Selalu tambahkan satuan ke jumlahJson, bahkan jika jumlahnya 0
                    jumlahJson[satuanId] = jumlah;

                    if (jumlah > 0 && satuanId) {
                        isAnyQuantityGreaterThanZero = true;
                        const harga = parseFloat(jumlahInput.dataset.hargaSatuan) || 0;
                        totalHargaProduk += jumlah * harga;
                    }
                });

                // Update status checkbox dan class 'selected'
                const checkbox = card.querySelector('.item-checkbox');
                if (isAnyQuantityGreaterThanZero) {
                    checkbox.checked = true;
                    card.classList.add('selected');
                } else {
                    checkbox.checked = false;
                    card.classList.remove('selected');
                }

                // Kirim update ke backend
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
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
                            alert(data.message || 'Update gagal.'); // Tampilkan alert dari backend

                            // --- START PERUBAHAN UTAMA DI SINI ---
                            // Jika ada data revert_jumlah_json, terapkan ke input fields
                            if (data.revert_jumlah_json) {
                                // Untuk memastikan grup satuan yang ditampilkan adalah yang relevan dari revert_jumlah_json
                                // kita perlu sedikit lebih cerdas:
                                // 1. Hapus semua grup satuan yang ada kecuali yang pertama
                                // 2. Set nilai grup pertama sesuai data revert
                                // 3. Tambahkan grup baru jika ada satuan lain di revert_jumlah_json

                                const jumlahSatuanWrapper = card.querySelector('.jumlah-satuan-wrapper');
                                const existingGroups = jumlahSatuanWrapper.querySelectorAll('.satuan-group');

                                // Hapus semua kecuali grup pertama
                                for (let i = existingGroups.length - 1; i > 0; i--) {
                                    existingGroups[i].remove();
                                }
                                const firstGroup = existingGroups[0];
                                // Pastikan tombol hapus untuk grup pertama disembunyikan jika hanya satu grup nantinya
                                firstGroup.querySelector('.hapus-jumlah')?.classList.add('d-none');

                                let isFirstGroupSet = false;
                                for (const satuanId in data.revert_jumlah_json) {
                                    const availableQty = data.revert_jumlah_json[satuanId];

                                    if (!isFirstGroupSet) {
                                        // Set grup pertama
                                        const jumlahInputFirst = firstGroup.querySelector('.jumlah-input');
                                        const satuanSelectFirst = firstGroup.querySelector('.satuan-select');

                                        if (jumlahInputFirst && satuanSelectFirst) {
                                            jumlahInputFirst.value = availableQty;
                                            satuanSelectFirst.value = satuanId;
                                            // Update data-harga-satuan di input agar konsisten
                                            const selectedOption = satuanSelectFirst.options[satuanSelectFirst.selectedIndex];
                                            jumlahInputFirst.dataset.hargaSatuan = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
                                        }
                                        isFirstGroupSet = true;
                                    } else {
                                        // Kloning grup pertama dan tambahkan untuk satuan lainnya
                                        const clone = firstGroup.cloneNode(true);
                                        const jumlahInputClone = clone.querySelector('.jumlah-input');
                                        const satuanSelectClone = clone.querySelector('.satuan-select');

                                        if (jumlahInputClone && satuanSelectClone) {
                                            jumlahInputClone.value = availableQty;
                                            satuanSelectClone.value = satuanId;
                                            const selectedOption = satuanSelectClone.options[satuanSelectClone.selectedIndex];
                                            jumlahInputClone.dataset.hargaSatuan = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
                                        }
                                        clone.querySelector('.hapus-jumlah')?.classList.remove('d-none'); // Tampilkan tombol hapus
                                        jumlahSatuanWrapper.appendChild(clone);
                                        bindSatuanGroupEvents(clone); // Ikat event untuk kloningan baru
                                    }
                                }

                                // Pastikan tombol hapus untuk semua grup sudah terlihat jika ada lebih dari 1
                                if (Object.keys(data.revert_jumlah_json).length > 1) {
                                    jumlahSatuanWrapper.querySelectorAll('.hapus-jumlah').forEach(btn => btn.classList.remove('d-none'));
                                } else if (Object.keys(data.revert_jumlah_json).length === 1) {
                                    // Jika hanya ada satu satuan yang direvert (atau setelah revert hanya ada satu), pastikan tombol hapus tersembunyi
                                    jumlahSatuanWrapper.querySelector('.hapus-jumlah')?.classList.add('d-none');
                                }
                            }
                            // --- END PERUBAHAN UTAMA DI SINI ---

                            // Setelah semua input di-reset/diperbarui, panggil hitungTotal untuk memperbarui UI
                            // dan updateCheckoutForms untuk sinkronisasi form.
                            hitungTotal();
                            updateCheckoutForms();

                        } else {
                            // Jika sukses, hitung total dan update form checkout seperti biasa
                            hitungTotal();
                            updateCheckoutForms();
                        }
                    })
                    .catch(err => {
                        console.error('Update error:', err);
                        alert('Terjadi kesalahan saat update jumlah.');
                        // Untuk keamanan, mungkin perlu juga mengupdate total secara konservatif.
                        hitungTotal();
                        updateCheckoutForms();
                    });
            }

            // Fungsi untuk menghitung dan memperbarui total produk dan harga
            function hitungTotal() {
                let total = 0;
                let adaYangDipilih = false;

                document.querySelectorAll('.card').forEach(card => {
                    const checkbox = card.querySelector('.item-checkbox');
                    if (checkbox && checkbox.checked) {
                        adaYangDipilih = true;
                        card.querySelectorAll('.satuan-group').forEach(group => {
                            const jumlahInput = group.querySelector('.jumlah-input');
                            const harga = parseFloat(jumlahInput.dataset.hargaSatuan) || 0;
                            const jumlah = parseInt(jumlahInput.value) || 0;
                            total += jumlah * harga;
                        });
                    }
                });

                document.getElementById('totalKeranjang').textContent = formatRupiah(total);
                document.getElementById('totalKeranjangDesktop').textContent = formatRupiah(total);

                const formMobile = document.getElementById('checkoutForm');
                const formDesktop = document.getElementById('checkoutFormDesktop');

                const btnMobile = formMobile?.querySelector('button[type="submit"]');
                const btnDesktop = formDesktop?.querySelector('button[type="submit"]');

                if (btnMobile) btnMobile.disabled = !adaYangDipilih;
                if (btnDesktop) btnDesktop.disabled = !adaYangDipilih;
            }

            // Fungsi untuk mengupdate input hidden di form checkout
            function updateCheckoutForms() {
                const selectedCards = document.querySelectorAll('.card.selected');
                const formMobile = document.getElementById('checkoutForm');
                const formDesktop = document.getElementById('checkoutFormDesktop');

                // Hapus input hidden sebelumnya
                formMobile?.querySelectorAll('input[name="keranjang_id[]"]').forEach(e => e.remove());
                formDesktop?.querySelectorAll('input[name="keranjang_id[]"]').forEach(e => e.remove());

                // Tambahkan input hidden baru
                selectedCards.forEach(card => {
                    const keranjangId = card.querySelector('.item-checkbox').dataset.id;
                    if (!keranjangId) return;

                    const inputMobile = document.createElement('input');
                    inputMobile.type = 'hidden';
                    inputMobile.name = 'keranjang_id[]';
                    inputMobile.value = keranjangId;
                    formMobile?.appendChild(inputMobile);

                    const inputDesktop = inputMobile.cloneNode(true);
                    formDesktop?.appendChild(inputDesktop);
                });
            }

            // Fungsi untuk memvalidasi tombol checkout hanya aktif jika ada yang dipilih
            function preventSubmitIfEmpty(formId) {
                const form = document.getElementById(formId);
                if (!form) return;

                form.addEventListener('submit', function(e) {
                    const selected = document.querySelectorAll('.item-checkbox:checked');
                    if (selected.length === 0) {
                        e.preventDefault();
                        alert('Pilih minimal satu produk sebelum checkout.');
                    }
                });
            }

            // --- Inisialisasi Event Listeners ---
            // Ikat event untuk semua grup satuan yang ada saat halaman dimuat
            document.querySelectorAll('.satuan-group').forEach(bindSatuanGroupEvents);

            // Event listener untuk SEMUA elemen "Pilih Semua" (desktop dan mobile)
            document.querySelectorAll('.select-all').forEach(function(selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                        checkbox.checked = isChecked;
                        const card = checkbox.closest('.card');
                        if (isChecked) {
                            card.classList.add('selected');
                        } else {
                            card.classList.remove('selected');
                        }
                    });
                    hitungTotal();
                    updateCheckoutForms();
                });
            });

            // Event listener untuk checkbox produk individual (select/deselect)
            document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const card = this.closest('.card');

                    if (this.checked) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                    hitungTotal();
                    updateCheckoutForms();
                });
            });

            // Event listener untuk mengarahkan ke halaman detail produk
            document.querySelectorAll('.card').forEach(card => {
                card.addEventListener('click', function(event) {
                    const interactiveElements = [
                        '.custom-check-item', // Checkbox
                        '.minus-btn', // Tombol minus
                        '.plus-btn', // Tombol plus
                        '.tambah-jumlah', // Tombol tambah satuan
                        '.hapus-jumlah', // Tombol hapus satuan
                        '.jumlah-input', // Input angka (jumlah)
                        '.satuan-select', // Dropdown satuan
                        'form button[type="submit"]', // Tombol submit dalam form, e.g. tombol delete
                        'a' // Any anchor tag inside the card that should handle its own navigation
                    ];

                    let isInteractiveClick = false;
                    for (const selector of interactiveElements) {
                        // Gunakan .closest() di sini juga untuk memastikan deteksi yang akurat
                        if (event.target.closest(selector)) {
                            isInteractiveClick = true;
                            break;
                        }
                    }

                    if (!isInteractiveClick) {
                        const produkIdInput = card.querySelector('.jumlah-input');
                        if (produkIdInput && produkIdInput.dataset.produkId) {
                            const produkId = produkIdInput.dataset.produkId;
                            window.location.href = `{{ url('/pelanggan-area/detail_produk') }}/${produkId}`;
                        } else {
                            console.warn('Produk ID tidak ditemukan di kartu ini atau data-produk-id belum disetel.');
                        }
                    }
                });
            });

            // Validasi form sebelum submit
            preventSubmitIfEmpty('checkoutForm');
            preventSubmitIfEmpty('checkoutFormDesktop');

            // Tambahkan hidden input sebelum submit
            document.getElementById('checkoutForm')?.addEventListener('submit', updateCheckoutForms);
            document.getElementById('checkoutFormDesktop')?.addEventListener('submit', updateCheckoutForms);

            // Jalankan pertama kali
            hitungTotal();
            updateCheckoutForms();
        });
    </script>

    @endsection