<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
    <title></title>
    <!-- [Meta] -->
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Mantis is made using Bootstrap 5 design framework. Download the free admin template & use it for your project.">
    <meta name="keywords" content="Mantis, Dashboard UI Kit, Bootstrap 5, Admin Template, Admin Dashboard, CRM, CMS, Bootstrap Admin Template">
    <meta name="author" content="CodedThemes">

    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ asset('template/dist') }}/assets/images/favicon.svg" type="image/x-icon"> <!-- [Google Font] Family -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="{{ asset('template/dist') }}/assets/fonts/tabler-icons.min.css">
    <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="{{ asset('template/dist') }}/assets/fonts/feather.css">
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="{{ asset('template/dist') }}/assets/fonts/fontawesome.css">
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="{{ asset('template/dist') }}/assets/fonts/material.css">
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('template/dist') }}/assets/css/style.css" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('template/dist') }}/assets/css/style-preset.css">

    <!-- Hias Table Link: https://datatables.net/ -->
    <link href="//cdn.datatables.net/2.3.0/css/dataTables.dataTables.min.css" rel="stylesheet">
    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    <style>
        /* SEMBUNYIKAN SEMUA ELEMEN LARAVEL DEBUGBAR */
        .phpdebugbar,
        .phpdebugbar-open-btn,
        .phpdebugbar-minimize-btn,
        .phpdebugbar-restore-btn {
            display: none !important;
        }


        .custom-pagination .dataTables_paginate {
            margin-right: 10px;
        }

        #pc-sidebar {
            transition: all 0.3s ease;
            width: 250px;
        }

        #pc-sidebar.collapsed {
            width: 70px;
            overflow: hidden;
        }

        #pc-sidebar.collapsed .pc-mtext,
        #pc-sidebar.collapsed .b-brand span {
            display: none !important;
        }

        #pc-sidebar.collapsed .pc-micon {
            margin-right: 0 !important;
            justify-content: center;
        }

        .pc-container {
            transition: margin-left 0.3s ease;
            margin-left: 250px;
        }

        #pc-sidebar.collapsed~.pc-container {
            margin-left: 70px;
        }

        @media (max-width: 768px) {
            #pc-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: 250px;
                background-color: rgb(125, 191, 217);
                transition: transform 0.3s ease;
                transform: translateX(-100%);
                z-index: 1050;
            }

            #pc-sidebar.active {
                transform: translateX(0);
            }

            .pc-container {
                margin-left: 0 !important;
                transition: none;
            }

            body.sidebar-open {
                overflow: hidden;
            }
        }

        .pc-container {
            transition: margin-left 0.3s ease;
            margin-left: 250px;
        }

        .pc-container.collapsed {
            margin-left: 70px;
        }

        .dataTables_filter input {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .dashboard-section {
            border: 2px solid #5a9bd8;
            /* Biru muda */
            padding: 1rem;
            /* Padding sekitar 16px */
            margin-bottom: 1.5rem;
            /* Jarak bawah antar section */
            border-radius: 0.5rem;
            /* Sudut melengkung */
            background-color: #f9fbff;
            /* Latar belakang sangat terang */
        }
    </style>
</head>
<!-- [Head] end -->

<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-direction="ltr" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ Sidebar Menu ] start -->
    <x-sidebar />
    <!-- [ Sidebar Menu ] end -->

    <!-- [ Header Topbar ] start -->
    <x-header />
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->

    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-block mt-3">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <ul class="breadcrumb">
                            @yield('breadcrumb')
                        </ul>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->

            <!-- [ Main Content ] start -->
            <div class="row" style="box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);">
                <div class="">
                    @if(session('error'))
                    <div class="alert alert-danger" id="error-alert">
                        {{ session('error') }}
                    </div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success" id="success-alert">
                        {{ session('success') }}
                    </div>
                    @endif
                </div>

                @yield('content')
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    <x-footer />

    <!-- untuk table -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>

    <script>
        $(document).ready(function() {


            $('#table').DataTable({
                language: {
                    search: "🔍",
                    searchPlaceholder: "Search here...",
                },
                dom: '<"row mb-3"' +
                    '<"col-md-6"l>' +
                    '<"col-md-6 text-end"f>' +
                    '>' +
                    '<"row"' +
                    '<"col-sm-12"tr>' +
                    '>' +
                    '<"row mt-3"' +
                    '<"col-md-6"i>' +
                    '<"col-md-6 text-end custom-pagination"p>' +
                    '>',
            });

            // ================================================================
            // KODE BARU UNTUK ALERT OTOMATIS HILANG DIMULAI DI SINI
            // ================================================================

            // Fungsi untuk menyembunyikan alert setelah beberapa detik
            function hideAlert(alertId, delay = 4000) { // delay dalam milidetik (4000 ms = 4 detik)
                const $alert = $(alertId);
                if ($alert.length) { // Pastikan elemen alert ada di DOM
                    setTimeout(function() {
                        $alert.fadeOut('slow', function() {
                            $(this).remove(); // Hapus elemen dari DOM setelah fade out
                        });
                    }, delay);
                }
            }

            // Panggil fungsi untuk alert success dan error
            hideAlert('#success-alert', 4000); // Sembunyikan alert success setelah 4 detik
            hideAlert('#error-alert', 5000); // Sembunyikan alert error setelah 5 detik (bisa diatur beda)


            // ... kode DataTables dan alert...

            const sidebar = document.getElementById('pc-sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const mobileToggle = document.getElementById('mobile-collapse');
            const container = document.querySelector('.pc-container');

            // Desktop sidebar collapse state from localStorage
            const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

            function openMobileSidebar() {
                sidebar.classList.add('active');
                document.body.classList.add('sidebar-open');
            }

            function closeMobileSidebar() {
                sidebar.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }

            // Initial sidebar state
            if (window.innerWidth > 768) {
                // Desktop: apply collapsed state
                if (isSidebarCollapsed) {
                    sidebar.classList.add('collapsed');
                    container.classList.add('collapsed');
                } else {
                    container.classList.remove('collapsed');
                }
            } else {
                // Mobile: sidebar hidden by default
                sidebar.classList.remove('collapsed');
                container.classList.remove('collapsed');
                closeMobileSidebar();
            }

            // Toggle for desktop collapse
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('collapsed');
                    container.classList.toggle('collapsed');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
            }

            // Toggle for mobile open/close
            if (mobileToggle) {
                mobileToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (sidebar.classList.contains('active')) {
                        closeMobileSidebar();
                    } else {
                        openMobileSidebar();
                    }
                });
            }

            // Close sidebar on outside click in mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (sidebar.classList.contains('active') && !sidebar.contains(event.target) && !mobileToggle.contains(event.target)) {
                        closeMobileSidebar();
                    }
                }
            });

            // Update on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeMobileSidebar();
                    if (localStorage.getItem('sidebarCollapsed') === 'true') {
                        sidebar.classList.add('collapsed');
                        container.classList.add('collapsed');
                    } else {
                        sidebar.classList.remove('collapsed');
                        container.classList.remove('collapsed');
                    }
                    document.body.classList.remove('sidebar-open');
                } else {
                    sidebar.classList.remove('collapsed');
                    container.classList.remove('collapsed');
                    closeMobileSidebar();
                }
            });
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('scripts')
    @yield('scripts')

    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/bootstrap.min.js"></script>
    <script src="../assets/js/fonts/custom-font.js"></script>
    <script src="../assets/js/pcoded.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>

    <script>
        layout_change('light');
        change_box_container('false');
        layout_rtl_change('false');
        preset_change("preset-1");
        font_change("Public-Sans");
    </script>

    <!-- CDN Popper dan Bootstrap 5 JS (langsung dari sumber resmi) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
<!-- [Body] end -->

</html>