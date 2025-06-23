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
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .footer-info .small {
            font-size: 0.9rem;
        }

        .info-footer {
            font-size: 0.88rem;
            line-height: 1.5;
            border-top: 1px solid #0f456f;
        }

        .info-footer a:hover {
            text-decoration: none;
            opacity: 0.85;
        }

        .text-primary-custom {
            color: #135291 !important;
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
            font-weight: 500;
            transition: color 0.2s, background 0.2s;
        }

        .header-nav .nav-link.active,
        .header-nav .nav-link:focus,
        .header-nav .nav-link:hover {
            color: #135291 !important;
            background: none !important;
            border-bottom: 3px solid #135291;
            font-weight: 700;
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

        /* Footer Nav Mobile */
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
            font-weight: 500;
            color: #000;
            transition: color 0.2s;
        }

        .footer-nav-btn.active .bi,
        .footer-nav-btn.active span {
            color: #135291 !important;
            font-weight: 700;
        }

        .footer-nav-btn:not(.active) .bi,
        .footer-nav-btn:not(.active) span {
            color: #000 !important;
            font-weight: 500;
        }

        /* Tombol tengah (floating) */
        .footer-nav-btn.center-btn {
            position: relative;
            top: -22px;
            width: 54px;
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

        .footer-nav-btn.center-btn svg {
            width: 38px !important;
            height: 38px !important;
            display: block;
        }

        /* Main content wrapper */
        .main-content {
            min-height: 100vh;
            padding-top: 80px;
            padding-bottom: 65px;
            /* <– disesuaikan dari sebelumnya 90 */
        }

        @media (max-width: 768px) {
            .main-content {
                padding-top: 0;
                padding-bottom: 65px;
                /* <– disesuaikan dari sebelumnya 75 */
            }
        }

        @media (max-width: 768px) {

            /* Jam & HP berdampingan */
            .footer-info .jam-operasional,
            .footer-info .nomor-wa {
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }

            /* Alamat di bawah, 100% lebar */
            .footer-info .alamat-footer {
                flex: 0 0 100% !important;
                max-width: 100% !important;
                margin-top: 10px;
            }

            /* Perbaiki align center */
            .footer-info .d-flex {
                justify-content: center !important;
                text-align: center;
            }

            /* Perkecil ukuran font khusus mobile */
            .footer-info span,
            .footer-info a {
                font-size: 0.75rem !important;
                /* kira-kira setara 12px */
            }
        }

        /* Di desktop (lebih besar dari 991px) - hapus jarak bawah */
        @media (min-width: 992px) {
            .footer-info {
                margin-bottom: 0 !important;
                padding-bottom: 0 !important;
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
                <div class="header-greeting-subtitle">
                    @switch($activeMenu)
                    @case('home') Katalog Produk @break
                    @case('keranjang') Keranjang Saya @break
                    @case('formcepat') Form Belanja Cepat @break
                    @case('riwayat') Riwayat Belanja Saya @break
                    @case('profile') Profile Saya @break
                    @default Toko KZ Family
                    @endswitch
                </div>
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
                    <a href="{{ route('mobile.form_belanja_cepat.index') }}" class="nav-link px-3 {{ ($activeMenu ?? '') == 'formcepat' ? 'active' : '' }}">Form Cepat</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mobile.riwayat_belanja.index') }}" class="nav-link px-3 {{ ($activeMenu ?? '') == 'riwayat' ? 'active' : '' }}">Riwayat</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mobile.profile_pelanggan.index') }}" class="nav-link px-3 {{ ($activeMenu ?? '') == 'profile' ? 'active' : '' }}">Profile</a>
                </li>
            </ul>
        </div>

        <div class="header-cart">
            <a href="{{ route('mobile.keranjang.index') }}">
                <i class="bi bi-cart {{ ($activeMenu ?? '') == 'keranjang' ? 'text-primary-custom fw-bold' : 'text-dark' }}"></i>
            </a>
        </div>

    </nav>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer Informasi -->
    <footer class="footer-info text-white pt-4 pb-5" style="background: #135291; z-index: 1; position: relative;">
        <div class="container text-center">

            <div class="row justify-content-center gy-3 mb-3 px-2">
                <!-- Jam Operasional -->
                <div class="col-6 col-md-4 d-flex justify-content-center align-items-center gap-2 text-center order-1 order-md-1">
                    <i class="bi bi-clock-fill fs-5"></i>
                    <span class="small">Setiap Hari, 08.00–21.00</span>
                </div>

                <!-- WhatsApp -->
                <div class="col-6 col-md-4 d-flex justify-content-center align-items-center gap-2 text-center order-2 order-md-3">
                    <i class="bi bi-whatsapp fs-5"></i>
                    <a href="https://wa.me/6285263264699" class="small text-white text-decoration-none">
                        0852-6326-4699
                    </a>
                </div>

                <!-- Alamat -->
                <div class="col-12 col-md-4 d-flex justify-content-center align-items-center gap-2 text-center order-3 order-md-2">
                    <i class="bi bi-geo-alt-fill fs-5"></i>
                    <a href="https://www.google.com/maps/place/KZ+Family+Ophir/@0.0269374,99.8146812,17z/data=!3m1!4b1!4m6!3m5!1s0x302a7ff85c9f1619:0x92f29e6e1199cce0!8m2!3d0.0269374!4d99.8172561!16s%2Fg%2F11vb7jnx9k?entry=ttu&g_ep=EgoyMDI1MDYxNy4wIKXMDSoASAFQAw%3D%3D"
                        class="small text-white text-decoration-none" target="_blank">
                        Ophir Barat, Koto Baru, Luhak Nan Duo, Pasaman Barat
                    </a>
                </div>
            </div>

            <hr style="opacity: 0.2;">
            <p class="small mb-0 ">© {{ date('Y') }} <strong>Toko KZ Family</strong>. All rights reserved.</p>
        </div>
    </footer>






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
        <a href="{{ route('mobile.form_belanja_cepat.index') }}" class="footer-nav-btn center-btn {{ ($activeMenu ?? '') == 'formcepat' ? 'active' : '' }}">
            <img src="{{ asset('storage/logo/' . (($activeMenu ?? '') == 'formcepat' ? 'form_cepat_biru.png' : 'form_cepat.png')) }}"
                alt="Form Cepat Aktif"
                style="width:44px; height:44px; object-fit:contain;" />
        </a>

        <a href="{{ route('mobile.riwayat_belanja.index') }}" class="footer-nav-btn {{ ($activeMenu ?? '') == 'riwayat' ? 'active' : '' }}">
            <i class="bi bi-list-ul"></i>
            <span>Riwayat</span>
        </a>
        <a href="{{ route('mobile.profile_pelanggan.index') }}" class="footer-nav-btn {{ ($activeMenu ?? '') == 'profile' ? 'active' : '' }}">
            <!-- Cek apakah foto ada, jika tidak tampilkan ikon -->
            @if(auth()->user()->foto_user)
            <img src="{{ asset('storage/' . auth()->user()->foto_user) }}" alt="Foto Profile" style="width:44px; height:44px; border-radius:50%; object-fit:cover;">
            @else
            <i class="bi bi-person" style="font-size: 1.5rem;"></i>
            @endif
            <span>Profile</span>
        </a>

    </nav>


    @stack('body')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>


</html>