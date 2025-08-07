<div>
    <nav class="pc-sidebar" id="pc-sidebar" style="box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4)">
        <div class="navbar-wrapper d-flex flex-column" style="height: 100%;">
            <div class="m-header" style="background-color: white; border-bottom: 2px solid #ddd; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); height: 100px; padding: 10px 20px;">
                <a class="b-brand text-primary d-flex text-decoration-none align-items-center">
                    <!-- Logo -->
                    <img src="{{ asset('storage/logo/LogoKZ_transparant.png') }}"
                        class="img-fluid logo-lg me-2"
                        style="width: 70px; height: auto;"
                        alt="Logo KZ Family">


                    <span style="font-weight: bold; color: #000000; line-height: 1.2;">
                        <span style="display: block; font-size: 0.88rem; position: relative; padding-bottom: 8px; margin-bottom: 5px;">
                            SISTEM PENJUALAN
                            <span style="position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background-color: #000000;"></span>
                        </span>
                        <span style="display: block; font-size: 0.85rem;">MANAJEMEN STOK</span>
                    </span>
                </a>
            </div>


            <div class="navbar-content flex-grow-1 d-flex flex-column justify-content-between">
                <!-- Menu -->
                <ul class="pc-navbar">

                    <x-sidebar.links title="Dashboard" icon="ti ti-dashboard" route='dashboard.index' />
                    <x-sidebar.links title="Kasir" icon="fas fa-cash-register" route='transaksi_offline.create' />


                    <li class="pc-caption text-uppercase mt-0 mb-1 ps-3 fw-semibold" style="font-size: 0.65rem;">
                        Sistem Penjualan
                    </li>
                    <x-sidebar.links title="Transaksi Offline" icon="ti ti-building-store" route='transaksi_offline.index' />
                    <x-sidebar.links title="Transaksi Online" icon="ti ti-credit-card" route='transaksi_online.index' />

                    <li class="pc-caption text-uppercase mt-0 mb-1 ps-3 fw-semibold" style="font-size: 0.65rem;">
                        Manajemen Stok
                    </li>
                    <x-sidebar.links title="Produk" icon="ti ti-package" route='produk.index' />
                    <x-sidebar.links title="Mutasi Stok" icon="ti ti-stack" route='stok.index' />

                    <li class="pc-caption text-uppercase mt-0 mb-1 ps-3 fw-semibold" style="font-size: 0.65rem;">
                        Lain-lain
                    </li>
                    <x-sidebar.links title="Manajemen Pelanggan" icon="ti ti-users" route='pelanggan.index' />
                    <x-sidebar.links title="Keuangan" icon="ti ti-currency-dollar" route='keuangan.index' />
                    <x-sidebar.links title="Keranjang Pelanggan" icon="ti ti-shopping-cart" route="keranjang.index" />
                    <x-sidebar.links title="Banner" icon="ti ti-photo" route="banner.index" />

                </ul>


                <!-- Logout di bawah -->
                <ul class="pc-navbar mt-auto">
                    <li class="pc-item">
                        <form action="{{ route('logout') }}" method="POST" class="pc-link">
                            @csrf
                            <button type="submit" class="pc-link btn btn-link text-start w-100 px-0 logout">
                                <span class="pc-micon"><i class="ti ti-logout"></i></span>
                                <span class="pc-mtext">Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>

<style>
    .pc-navbar .pc-item .pc-link {
        padding-top: 2px;
        padding-bottom: 2px;
        font-size: 0.85rem;
        line-height: 1.2;
    }

    .pc-caption {

        margin-top: 2px !important;
        margin-bottom: 2px !important;
        font-size: 0.8rem !important;
        letter-spacing: 0.02em;
    }


    /* Set background untuk sidebar */
    .pc-sidebar {
        background-color: rgb(125, 191, 217);
        /* Biru langit */
    }

    /* Untuk ikon dan teks pada item aktif */
    .pc-link.active .pc-micon i,
    .pc-link.active .pc-mtext {
        color: #fff !important;
    }

    /* Efek hover pada item sidebar */
    .pc-link:hover {
        background-color: rgba(135, 206, 235, 0.27);
        /* Biru langit dengan transparansi */
        /* Warna background hover */
    }

    /* Pencocokan warna untuk Logout */
    .pc-link.logout:hover {
        background-color: #ff4d4d;
        /* Warna merah saat hover pada logout */
        color: white;
        /* Teks menjadi putih saat hover pada logout */
    }

    /* Transisi halus untuk perubahan background pada hover dan aktif */
    .pc-link,
    .pc-link.active {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Untuk memastikan Logout di bawah */
    .navbar-content {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Menyesuaikan ukuran header */
    .m-header {
        height: 100px;
        /* Besarkan header */
        padding: 20px;
        /* Memberi jarak di dalam header */
    }

    /* Menyesuaikan ukuran teks header */
    .m-header .b-brand span {
        font-size: 1.2rem;
    }
</style>