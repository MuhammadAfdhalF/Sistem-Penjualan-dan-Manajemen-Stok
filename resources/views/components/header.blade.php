<div>
    <header class="pc-header" style="box-shadow: 0 4px 6px -2px rgba(0, 0, 0, 0.3);">
        <div class="header-wrapper">
            <!-- [Mobile Media Block] start -->
            <div class="me-auto pc-mob-drp">
                <ul class="list-unstyled">
                    <!-- ======= Menu collapse Icon ===== -->
                    <li class="pc-h-item pc-sidebar-collapse">
                        <a href="#" class="pc-head-link ms-0" id="sidebar-toggle">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <li class="pc-h-item pc-sidebar-popup">
                        <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- [Mobile Media Block end] -->

            {{-- === BAGIAN BARU: Dropdown Profil Pengguna di Sudut Kanan Atas === --}}
            <div class="ms-auto"> {{-- Ini akan mendorong konten ke kanan --}}
                <ul class="list-unstyled">
                    {{-- Dropdown Notifikasi (opsional, jika ingin diaktifkan) --}}
                    {{-- <li class="dropdown pc-h-item">
                        <a
                            class="pc-head-link dropdown-toggle arrow-none me-0"
                            data-bs-toggle="dropdown"
                            href="#"
                            role="button"
                            aria-haspopup="false"
                            aria-expanded="false">
                            <i class="ti ti-mail"></i>
                        </a>
                        <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
                            <div class="dropdown-header d-flex align-items-center justify-content-between">
                                <h5 class="m-0">Message</h5>
                                <a href="#!" class="pc-head-link bg-transparent"><i class="ti ti-x text-danger"></i></a>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-header px-0 text-wrap header-notification-scroll position-relative" style="max-height: calc(100vh - 215px)">
                                <div class="list-group list-group-flush w-100">
                                    <a class="list-group-item list-group-item-action">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <img src="../assets/images/user/avatar-2.jpg" alt="user-image" class="user-avtar">
                                            </div>
                                            <div class="flex-grow-1 ms-1">
                                                <span class="float-end text-muted">3:00 AM</span>
                                                <p class="text-body mb-1">It's <b>Cristina danny's</b> birthday today.</p>
                                                <span class="text-muted">2 min ago</span>
                                            </div>
                                        </div>
                                    </a>
                                    <a class="list-group-item list-group-item-action">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <img src="../assets/images/user/avatar-1.jpg" alt="user-image" class="user-avtar">
                                            </div>
                                            <div class="flex-grow-1 ms-1">
                                                <span class="float-end text-muted">6:00 PM</span>
                                                <p class="text-body mb-1"><b>Aida Burg</b> commented your post.</p>
                                                <span class="text-muted">5 August</span>
                                            </div>
                                        </div>
                                    </a>
                                    <a class="list-group-item list-group-item-action">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <img src="../assets/images/user/avatar-3.jpg" alt="user-image" class="user-avtar">
                                            </div>
                                            <div class="flex-grow-1 ms-1">
                                                <span class="float-end text-muted">2:45 PM</span>
                                                <p class="text-body mb-1"><b>There was a failure to your setup.</b></p>
                                                <span class="text-muted">7 hours ago</span>
                                            </div>
                                        </div>
                                    </a>
                                    <a class="list-group-item list-group-item-action">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <img src="../assets/images/user/avatar-4.jpg" alt="user-image" class="user-avtar">
                                            </div>
                                            <div class="flex-grow-1 ms-1">
                                                <span class="float-end text-muted">9:10 PM</span>
                                                <p class="text-body mb-1"><b>Cristina Danny </b> invited to join <b> Meeting.</b></p>
                                                <span class="text-muted">Daily scrum meeting time</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <div class="text-center py-2">
                                <a href="#!" class="link-primary">View all</a>
                            </div>
                        </div>
                    </li> --}}

                    {{-- Dropdown Profil Pengguna --}}
                    <li class="dropdown pc-h-item header-user-profile">
                        <a
                            class="pc-head-link dropdown-toggle arrow-none me-0"
                            data-bs-toggle="dropdown"
                            href="#"
                            role="button"
                            aria-haspopup="false"
                            data-bs-auto-close="outside"
                            aria-expanded="false">
                            {{-- Ganti dengan ikon pengguna --}}
                            <i class="ti ti-user user-avtar" style="font-size: 1.5rem; line-height: 1; vertical-align: middle; margin-right: 5px;"></i> 
                            <span>{{ Auth::user()->nama ?? 'Admin' }}</span> {{-- Tampilkan nama user yang login --}}
                        </a>
                        <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                            <div class="dropdown-header">
                                <div class="d-flex mb-1">
                                    <div class="flex-shrink-0">
                                        {{-- Ikon pengguna di dalam header dropdown --}}
                                        <i class="ti ti-user user-avtar wid-35" style="font-size: 2rem;"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ Auth::user()->nama ?? 'Admin' }}</h6>
                                        <span>{{ Auth::user()->role ?? 'Role Tidak Diketahui' }}</span> {{-- Tampilkan role user --}}
                                    </div>
                                    {{-- Tombol Logout cepat di header dropdown --}}
                                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-header').submit();" class="pc-head-link bg-transparent">
                                        <i class="ti ti-power text-danger"></i>
                                    </a>
                                </div>
                            </div>
                            
                            {{-- Hapus tabs yang tidak diperlukan --}}
                            {{-- <ul class="nav drp-tabs nav-fill nav-tabs" id="mydrpTab" role="tablist"> ... </ul> --}}

                            <div class="tab-content" id="mysrpTabContent">
                                <div class="tab-pane fade show active" id="drp-tab-1" role="tabpanel" aria-labelledby="drp-t1" tabindex="0">
                                    {{-- Opsi Ganti Password --}}
                                    <a href="{{ route('password.change') }}" class="dropdown-item"> {{-- ASUMSI ROUTE INI ADA --}}
                                        <i class="ti ti-lock"></i>
                                        <span>Ganti Password</span>
                                    </a>
                                    {{-- Opsi Logout --}}
                                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-dropdown').submit();" class="dropdown-item">
                                        <i class="ti ti-power"></i>
                                        <span>Logout</span>
                                    </a>
                                    {{-- Hapus item lain yang tidak diperlukan --}}
                                    {{-- <a href="#!" class="dropdown-item"> ... </a> --}}
                                </div>
                                {{-- Hapus tab-pane lain jika tidak diperlukan --}}
                                {{-- <div class="tab-pane fade" id="drp-tab-2" role="tabpanel" aria-labelledby="drp-t2" tabindex="0"> ... </div> --}}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            {{-- === AKHIR BAGIAN BARU === --}}
        </div>
    </header>
</div>

{{-- Form Logout (penting untuk POST request) --}}
<form id="logout-form-header" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
<form id="logout-form-dropdown" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
