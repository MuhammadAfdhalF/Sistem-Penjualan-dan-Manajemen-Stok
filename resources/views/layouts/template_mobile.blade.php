<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bottom Nav Example')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f7f7f7;
            font-family: 'Inter', Arial, sans-serif;
        }

        .header-nav {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 101;
            background: #fff;
        }

        /* Nav Desktop Custom Color */
        .header-nav .nav-link {
            color: #222 !important;
            /* Default: hitam */
            font-weight: 500;
            transition: color 0.2s, background 0.2s;
        }

        .header-nav .nav-link.active,
        .header-nav .nav-link:focus,
        .header-nav .nav-link:hover {
            color: #135291 !important;
            /* Aktif/Biru Tua/Hover */
            background: none !important;
            border-bottom: 3px solid #135291;
            font-weight: 700;

            /* garis bawah biru */

        }

        .header-nav .header-greeting-title {
            font-size: 1.22rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        .header-nav .header-greeting-subtitle {
            font-size: 1rem;
            color: #666;
            font-weight: 400;
            line-height: 1;
        }

        .header-cart a {
            font-size: 1.9rem;
            text-decoration: none;
        }

        /* Hide footer on desktop */
        @media (min-width: 769px) and (pointer: fine) {
            .footer-mobile-nav {
                display: none !important;
            }
        }

        /* Hide header on mobile */
        @media (max-width: 768px),
        (pointer: coarse) {
            .header-nav {
                display: none !important;
            }

            .footer-mobile-nav {
                display: flex !important;
            }
        }

        .footer-mobile-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            background: #fff;
            box-shadow: 0 -2px 12px #0001;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 68px;
            z-index: 103;
            border-top: 1.2px solid #eee;
        }

        .footer-nav-btn {
            background: none;
            border: none;
            outline: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #000 !important;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 50%;
            padding: 0;
            transition: color 0.19s;
            text-decoration: none !important;

        }

        .footer-nav-btn .bi {
            font-size: 1.7rem;
            margin-bottom: 2px;
            color: #000;
            transition: color 0.2s;
        }

        .footer-nav-btn span {
            font-size: 0.97rem;
            margin-top: 0;
            font-weight: 500;
            color: #000;
            transition: color 0.2s;
        }

        .footer-nav-btn.active .bi,
        .footer-nav-btn.active span {
            color: #135291 !important;
            /* Biru untuk aktif */
            font-weight: 700;
        }

        .footer-nav-btn:not(.active) .bi,
        .footer-nav-btn:not(.active) span {
            color: #000 !important;
            /* Hitam untuk non-aktif */
            font-weight: 500;
        }

        .footer-mobile-nav .footer-nav-btn span {
            text-decoration: none !important;
            /* Hapus garis bawah */
        }

        /* Tombol tengah (floating) */
        /* Floating button tetap ukuran normal */
        .footer-nav-btn.center-btn {
            position: relative;
            top: -22px;
            width: 54px;
            /* Tidak diubah */
            height: 54px;
            background: #fff;
            box-shadow: 0 4px 16px #0002;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: none;
            padding: 0;
        }

        /* Icon SVG di dalam tombol tengah diperbesar */
        .footer-nav-btn.center-btn svg {
            width: 38px !important;
            /* Ukuran icon SVG lebih besar */
            height: 38px !important;
            display: block;
        }


        .main-content {
            min-height: 100vh;
            padding-top: 80px;
            padding-bottom: 90px;
        }

        @media (max-width: 768px) {
            .main-content {
                padding-top: 0;
                padding-bottom: 75px;
            }
        }
    </style>
    @stack('head')
</head>

<body>
    <!-- Header NAV (Only desktop) -->
    <nav class="header-nav d-flex align-items-center justify-content-between px-4 py-2">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('storage/logo/LogoKZ_transparant.png') }}" alt="Logo" style="height:58px;">
            <div>
                <div class="header-greeting-title">Toko KZ Family</div>
                <div class="header-greeting-subtitle">Katalog Produk</div>
            </div>
        </div>
        <div class="d-flex align-items-center flex-grow-1 justify-content-center">
            <ul class="nav">
                <li class="nav-item">
                    <a href="{{ route('mobile.home.index') }}" class="nav-link px-3 {{ ($activeMenu ?? '') == 'home' ? 'active' : '' }}">Home</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mobile.keranjang.index') }}" class="nav-link px-3 {{ ($activeMenu ?? '') == 'keranjang' ? 'active' : '' }}">Keranjang</a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0);" class="nav-link px-3 {{ ($activeMenu ?? '') == 'formcepat' ? 'active' : '' }}">Form Cepat</a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0);" class="nav-link px-3 {{ ($activeMenu ?? '') == 'riwayat' ? 'active' : '' }}">Riwayat</a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0);" class="nav-link px-3 {{ ($activeMenu ?? '') == 'profile' ? 'active' : '' }}">Profile</a>
                </li>
            </ul>
        </div>

        <div class="header-cart">
            <a href="{{ route('mobile.keranjang.index') }}"><i class="bi bi-cart"></i></a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer NAV (Only mobile) -->
    <nav class="footer-mobile-nav d-flex justify-content-around align-items-center" style="display:flex;">
        <a href="{{ route('mobile.home.index') }}" class="footer-nav-btn {{ ($activeMenu ?? '') == 'home' ? 'active' : '' }}">
            <i class="bi bi-house-door"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('mobile.keranjang.index') }}" class="footer-nav-btn {{ ($activeMenu ?? '') == 'keranjang' ? 'active' : '' }}">
            <i class="bi bi-cart"></i>
            <span>Keranjang</span>
        </a>
        <a href="javascript:void(0);" class="footer-nav-btn center-btn {{ ($activeMenu ?? '') == 'formcepat' ? 'active' : '' }}">
            <img src="{{ asset('storage/logo/form_cepat.png') }}" alt="Form Cepat Aktif" style="width:44px; height:44px; object-fit:contain;" />
        </a>
        <a href="javascript:void(0);" class="footer-nav-btn {{ ($activeMenu ?? '') == 'riwayat' ? 'active' : '' }}">
            <i class="bi bi-list-ul"></i>
            <span>Riwayat</span>
        </a>
        <a href="javascript:void(0);" class="footer-nav-btn {{ ($activeMenu ?? '') == 'profile' ? 'active' : '' }}">
            <i class="bi bi-person"></i>
            <span>Profile</span>
        </a>
    </nav>

    @stack('body')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>


</html>