<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bottom Nav Example')</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f7f7f7;
        }

        .main-content {
            min-height: 100vh;
            padding-top: 60px;
            /* Padding for desktop */
            padding-bottom: 92px;
            /* Space for nav */
        }

        /* Override padding-top and margin-top for mobile devices */
        @media (max-width: 768px) {
            .main-content {
                padding-top: 0;
                /* Remove padding-top on mobile */
                margin-top: 0;
                /* Ensure no margin-top on mobile */
            }
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 101;
        }

        .header-logo {
            display: flex;
            align-items: center;
            padding-left: 32px;
            gap: 16px;
        }

        .header-logo img {
            height: 60px;
            width: auto;
        }

        .header-greeting {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .header-greeting-title {
            font-size: 1.22rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        .header-greeting-subtitle {
            font-size: 1rem;
            color: #666;
            font-weight: 400;
            line-height: 1;
        }


        .header nav {
            display: flex;
            gap: 32px;
            align-items: center;
            /* RATA TENGAH VERTIKAL semua link */
        }

        .header nav a {
            color: #8a8a8a;
            /* Abu-abu elegan */
            font-size: 1.2rem;
            font-weight: 500;
            text-decoration: none;
            line-height: 42px;
            /* SAMA untuk semua menu (atur ke tinggi menu aktif) */
            min-height: 42px;
            /* Pastikan sama dengan tinggi menu aktif */
            display: flex;
            /* Agar vertikal align center */
            align-items: center;
            transition: color 0.3s, background 0.18s;
        }

        .header nav a:hover {
            color: #135291;
            /* ABU SEDIKIT LEBIH TUA SAAT HOVER */
        }

        .header nav a.active {
            background: #135291;
            color: #fff !important;
            border-radius: 22px;
            padding: 0 22px;
            /* Padding hanya kanan kiri, atas bawah = 0 */
            font-weight: 700;
            box-shadow: 0 2px 8px #13529122;
            min-height: 42px;
            line-height: 42px;
            display: flex;
        }



        .footer-nav-btn.active,
        .footer-nav-btn:active {
            background: #135291 !important;
            /* Biru muda */
            color: #fff !important;
            /* Teks putih */
            border-radius: 50%;
            /* Supaya bulat penuh */
            width: 48px;
            /* Lebar & tinggi sama, agar bulat */
            height: 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px #c1e5ff55;
            /* Opsional: shadow lembut */
            padding: 0;
            /* Hilangkan padding default */
            margin: 0 6px;
            /* Jarak antar tombol */
            transition: background 0.18s, color 0.18s;
            position: relative;
            z-index: 2;
            font-size: 1.1rem;
        }

        /* Agar icon dan teks putih */
        .footer-nav-btn.active svg,
        .footer-nav-btn:active svg {
            stroke: #fff !important;
            fill: none;
        }

        /* Agar teks di bawah icon juga putih, atau bisa disembunyikan jika hanya ingin icon */
        .footer-nav-btn.active span,
        .footer-nav-btn:active span {
            color: #fff !important;
            font-size: 0.85rem;
            margin-top: 2px;
        }


        .header-cart {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 32px;
            /* Supaya icon tidak mepet kanan */
        }

        .header-cart a {
            display: flex;
            align-items: center;
        }

        @media (max-width: 768px) {
            .header {
                display: none;
            }
        }


        /* Footer Navigation (Visible only on mobile) */
        .footer-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            background: #ffff;
            box-shadow: 0 -2px 12px #0001;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 70px;
            z-index: 100;
        }

        /* Footer Navigation Button */
        .footer-nav-btn {
            background: none;
            border: none;
            outline: none;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #888888;
            /* Warna abu-abu */
            font-size: 0.98rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        footer-nav-btn svg {
            margin-bottom: 2px;
            width: 28px;
            height: 28px;
            stroke-width: 2.2;
            stroke: #888888;
            /* Warna abu-abu pada ikon */
        }

        .footer-nav-center {
            position: relative;
            top: -30px;
            background: #ffff;
            border-radius: 50%;
            box-shadow: 0 4px 16px #0002;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            border: 1px solid #7DBFD9;
            transition: box-shadow 0.18s;
        }

        .footer-nav-center svg {
            width: 32px;
            height: 32px;
        }

        .footer-nav-center:active {
            box-shadow: 0 2px 12px #19191925;
        }

        .footer-nav-btn span {
            font-size: 0.91rem;
            margin-top: 0px;
        }



        /* Hide Footer on Desktop */
        @media (min-width: 769px) {
            .footer-nav {
                display: none;
            }
        }

        /* Hide Header on Mobile */
        @media (max-width: 768px) {
            .header {
                display: none;
            }

            .footer-nav {
                display: flex;
            }
        }

        @media (max-width: 600px) {
            .footer-nav {
                height: 66px;
            }

            .footer-nav-center {
                width: 53px;
                height: 53px;
            }

            .footer-nav-btn svg {
                width: 24px;
                height: 24px;
            }
        }

        @media (pointer: coarse) {
            .header {
                display: none !important;
            }

            .footer-nav {
                display: flex !important;
            }
        }

        @media (min-width: 769px) and (pointer: fine) {
            .footer-nav {
                display: none !important;
            }
        }

        /* FOOTER NAV LEBIH KECIL SAAT LANDSCAPE DI MOBILE */
        @media (pointer: coarse) and (orientation: landscape) {
            .footer-nav {
                height: 46px;
            }

            .footer-nav-center {
                width: 37px;
                height: 37px;
                top: -18px;
            }

            .footer-nav-btn {
                font-size: 0.78rem;
            }

            .footer-nav-btn svg,
            .footer-nav-center svg {
                width: 19px !important;
                height: 19px !important;
            }

            .footer-nav-btn.active,
            .footer-nav-btn:active {
                width: 32px;
                height: 32px;
                font-size: 0.88rem;
            }

            .footer-nav-btn span {
                font-size: 0.72rem;
                margin-top: 0;
            }
        }
    </style>
    @stack('head')
</head>

<body>
    <!-- Header (Visible only on desktop) -->
    <header class="header">
        <div class="header-logo">
            <img src="{{ asset('storage/logo/LogoKZ_transparant.png') }}" alt="Logo">
            <div class="header-greeting">
                <div class="header-greeting-title">Hi, Acuyyy</div>
                <div class="header-greeting-subtitle">Katalog Produk</div>
            </div>
        </div>

        <nav>
            <a href="#" class="active">Home</a>
            <a href="#">Keranjang</a>
            <a href="#">Form Cepat</a>
            <a href="#">Riwayat</a>
            <a href="#">Profile</a>
        </nav>
        <div class="header-cart">
            <a href="#" style="font-size: 1.9rem; text-decoration:none;">🛒</a>
        </div>
    </header>





    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer Navigation (Visible only on mobile) -->
    <nav class="footer-nav">
        <button class="footer-nav-btn active">
            <!-- Home icon -->
            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M4 12l8-8 8 8M5 12v7a2 2 0 002 2h3m4 0h3a2 2 0 002-2v-7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span>Home</span>
        </button>
        <button class="footer-nav-btn">
            <!-- Cart icon -->
            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <circle cx="9" cy="21" r="1" />
                <circle cx="20" cy="21" r="1" />
                <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span>Keranjang</span>
        </button>
        <button class="footer-nav-btn" style="flex:0">
            <span class="footer-nav-center">
                <!-- Icon form belanja cepat: clipboard + cart -->
                <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <rect x="6" y="3" width="12" height="18" rx="2" />
                    <path d="M9 7h6" stroke-linecap="round" />
                    <path d="M9 11h6" stroke-linecap="round" />
                    <circle cx="9" cy="19" r="1.2" />
                    <circle cx="15" cy="19" r="1.2" />
                </svg>
            </span>
        </button>

        <button class="footer-nav-btn">
            <!-- List/History icon -->
            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <line x1="8" y1="6" x2="21" y2="6" stroke-linecap="round" />
                <line x1="8" y1="12" x2="21" y2="12" stroke-linecap="round" />
                <line x1="8" y1="18" x2="21" y2="18" stroke-linecap="round" />
                <circle cx="4" cy="6" r="1.5" />
                <circle cx="4" cy="12" r="1.5" />
                <circle cx="4" cy="18" r="1.5" />
            </svg>
            <span>Riwayat</span>
        </button>
        <button class="footer-nav-btn">
            <!-- Profile icon -->
            <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="4" />
                <path d="M4 20c0-4 4-6 8-6s8 2 8 6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span>Profile</span>
        </button>
    </nav>

    @stack('body')
</body>

</html>