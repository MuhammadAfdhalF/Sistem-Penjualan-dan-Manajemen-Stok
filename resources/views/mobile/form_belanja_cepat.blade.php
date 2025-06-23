@extends('layouts.template_mobile')
@section('title', 'Form Belanja Cepat - KZ Family')

@push('head')
<style>
    /* STYLE ANDA TETAP SAMA, TIDAK DIUBAH */
    body {
        background-color: #f8f9fa;
    }

    .product-card {
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.21);
        margin-bottom: 1rem;
        transition: box-shadow 0.2s ease-in-out;
    }

    /* Optional hover effect */
    .product-card:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .product-card.selected {
        box-shadow: 0 4px 16px rgba(19, 82, 145, 0.25);
        border: 1px solid #135291;
    }

    .product-image {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
    }

    .custom-check {
        width: 20px;
        height: 20px;
        appearance: none;
        -webkit-appearance: none;
        background-color: #fff;
        border: 2px solid #135291;
        border-radius: 6px;
        cursor: pointer;
        position: relative;
        display: inline-block;
        transition: all 0.2s ease-in-out;
    }

    .custom-check:checked {
        background-color: #135291;
    }

    .custom-check:checked::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 6px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    @media (max-width: 768px) {
        .filter-wrapper {
            margin-left: 0.75rem;
            margin-right: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .filter-wrapper {
            /* Perintah ini tetap ada untuk memaksa filter menjadi satu baris */
            flex-wrap: nowrap !important;

        }

        /* Atur lebar untuk input pencarian (lebih besar) */
        .filter-wrapper .filter-input {
            width: 60%;
            /* Input pencarian mengambil 60% dari lebar */
            flex-grow: 0;
            /* Pastikan tidak tumbuh otomatis lagi, agar ukurannya pas 60% */
        }

        /* Atur lebar untuk grup kategori (lebih kecil) */
        .filter-wrapper .filter-select-group {
            width: 38%;
            /* Grup kategori mengambil sisa ruang (kurang sedikit untuk jarak/gap) */
        }

        main.main-content {
            margin-bottom: 0px;
            padding-bottom: 0px;
        }

    }

    @media (max-width: 991.98px) {
        .col-lg-8 {
            margin-bottom: 5rem !important;
        }
    }


    @media (max-width: 1280px) and (orientation: landscape) {
        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
    }

    @media (min-width: 600px) and (max-width: 1024px) {
        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
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
<div class="container-fluid px-0 px-lg-3 py-3 desktop-container " style="max-width: 1280px;">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 ms-3 mt-3 d-block d-lg-none">
        <div>
            <h6 class="fw-bold mb-1 text-body">Toko KZ Family</h6>
            <small class="text-muted">Form Belanja Cepat</small>
        </div>
        <div class="me-3">
            <a href="{{ route('mobile.keranjang.index') }}" class="btn bg-white shadow rounded-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-cart text-dark" style="font-size: 1.2rem;"></i>
            </a>
        </div>
    </div>

    <!-- Banner Welcome -->
    <div class="w-100 d-flex justify-content-center">
        <div class="alert alert-light text-center mx-auto" style="max-width: 700px; width: 95%; border: 1px solid rgba(0, 0, 0, 0.3); border-radius: 8px;">
            <div class="d-flex flex-column align-items-center">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-stars fs-5 me-2" style="color: #135291;"></i>
                    <strong class="fw-semibold text-dark">Selamat Datang di Cara Belanja Terbaik !!!</strong>
                </div>
                <p class="mb-0 small text-body-secondary">
                    Pilih semua produk kebutuhanmu secara grosir dan eceran, kemudian langsung checkout. Gak perlu lagi bolak-balik masukin ke keranjang!
                </p>
            </div>
        </div>
    </div>

    <!-- ===== FORM UNTUK FILTER ===== -->
    <form action="{{ route('mobile.form_belanja_cepat.index') }}" method="GET" id="filter-form">
        <div class="d-flex flex-wrap justify-content-between gap-1 mb-4 filter-wrapper">
            <div class="d-flex align-items-center shadow-sm px-3 flex-grow-1 filter-input" style="background-color: #fff; height: 44px; border: 1px solid rgba(0, 0, 0, 0.38); border-radius: 8px;">
                <span class="me-2" style="font-size: 1rem;">🔍</span>
                <input type="text" name="search" class="form-control border-0 shadow-none p-0" placeholder="Cari Produk diinginkan...." value="{{ $searchQuery ?? '' }}" style="font-size: clamp(0.75rem, 1.5vw, 1rem); background-color: transparent;">
            </div>

            {{-- Grup untuk Select dan Tombol Reset --}}
            <div class="d-flex gap-2 filter-select-group">
                <div class="flex-grow-1">
                    <select name="kategori" class="form-select shadow-sm w-100" style="height: 44px; font-size: clamp(0.75rem, 1.5vw, 0.95rem); border: 1px solid rgba(0, 0, 0, 0.38); border-radius: 8px;" onchange="this.form.submit()">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($listKategori as $k)
                        <option value="{{ $k }}" @if(isset($filterKategori) && $filterKategori==$k) selected @endif>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Reset, muncul jika ada filter aktif --}}
                @if(!empty($searchQuery) || !empty($filterKategori))
                <a href="{{ route('mobile.form_belanja_cepat.index') }}" class="btn btn-light d-flex align-items-center justify-content-center" title="Reset Filter" style="height: 44px; border: 1px solid rgba(0,0,0,0.38); width: 44px; border-radius: 8px;">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>

        </div>
    </form>
    <!-- ===== AKHIR FORM FILTER ===== -->


    <!-- ===== FORM UNTUK CHECKOUT ===== -->
    <form action="{{ route('mobile.form_belanja_cepat.konfirmasi') }}" method="POST" id="form-belanja-cepat">
        @csrf

        <!-- Layout Produk & Checkout -->
        <div class="row">
            <!-- Produk (kiri) -->
            <div class="col-lg-8">
                @forelse($produk as $p)
                <div class="card product-card p-3 mb-2 shadow-sm" data-produk-id="{{ $p->id }}">
                    <div class="d-flex align-items-center gap-3"> {{-- ubah align-items-start -> center --}}
                        <div class="d-flex align-items-center" style="height: 100%;">
                            <input type="checkbox" class="custom-check product-selector">
                        </div>
                        <img src="{{ $p->gambar ? asset('storage/gambar_produk/' . $p->gambar) : asset('assets/img/no-image.jpg') }}" class="product-image" alt="{{ $p->nama_produk }}">

                        <div class="flex-grow-1">
                            <h6 class="fw-semibold text-body mb- text-wrap">{{ $p->nama_produk }}</h6>

                            <div class="text-muted small mb-1">
                                @php
                                $hargaList = $p->satuans->map(function($satuan) use ($p) {
                                $hargaObj = $p->hargaProduks->firstWhere('satuan_id', $satuan->id);
                                if ($hargaObj) {
                                return 'Rp ' . number_format($hargaObj->harga, 0, ',', '.') . '/' . $satuan->nama_satuan;
                                }
                                return null;
                                })->filter()->toArray();
                                @endphp
                                {!! implode('<br>', $hargaList) !!}
                            </div>

                            <div class="text-muted small mb-2">Tersedia : {{ $p->stok_bertingkat }}</div>

                            <div class="jumlah-satuan-wrapper text-end">
                                <div class="row gx-2 align-items-center satuan-group mb-2 justify-content-end">
                                    <div class="col-auto">
                                        <div class="input-group input-group-sm" style="border: none;">
                                            <button class="btn btn-sm bg-light border-0 btn-minus" type="button" style="font-size: 0.75rem;">−</button>
                                            <input type="text" class="form-control text-center jumlah-input bg-light border-0" placeholder="0" value="" style="width: 45px; font-size: 0.75rem;">
                                            <button class="btn btn-sm bg-light border-0 btn-plus" type="button" style="font-size: 0.75rem;">+</button>
                                        </div>
                                    </div>
                                    <div class="col-auto" style="width: 80px;">
                                        <select class="form-select form-select-sm border-0 bg-light satuan-select" style="font-size: 0.75rem;">
                                            @foreach($p->satuans as $satuan)
                                            @php
                                            $hargaSatuan = $p->hargaProduks->firstWhere('satuan_id', $satuan->id);
                                            @endphp
                                            <option value="{{ $satuan->id }}" data-harga="{{ $hargaSatuan->harga ?? 0 }}">{{ $satuan->nama_satuan }}</option>
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
                            </div>
                        </div>
                    </div>
                </div>

                @empty
                <div class="text-center py-5">
                    <p class="text-muted">Produk tidak ditemukan.</p>
                </div>
                @endforelse
            </div>

            <!-- Checkout (kanan desktop saja) -->
            <div class="col-lg-4 d-none d-lg-block mt-3 mt-lg-0">
                <div class="bg-white p-4" style="box-shadow: 0 12px 42px rgba(0, 0, 0, 0.08), 0 3px 10px rgba(0, 0, 0, 0.04); border-radius: 18px;">
                    <div class="fw-semibold text-dark mb-1 fs-6">Total Produk : <span class="text-dark fw-bold" id="total-produk-desktop">0 produk</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                        <span class="text-secondary fs-6">Total</span>
                        <span class="fw-bold fs-5" style="color: #135291;" id="total-harga-desktop">Rp 0</span>
                    </div>
                    <button type="submit" class="btn w-100 fw-bold checkout-btn" style="border-radius: 12px; font-size: 1rem; padding: 12px 0; background-color: #135291; color: white;">
                        CHECKOUT !!!
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden inputs untuk data yang akan disubmit -->
        <div id="form-data-container"></div>
    </form>
    <!-- ===== AKHIR FORM CHECKOUT ===== -->


    <!-- Footer Mobile Checkout -->
    <div class="fixed-bottom bg-white border-top shadow-sm p-2 d-lg-none" style="bottom: 65px; z-index: 102;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-start">
                <small class="text-muted d-block">Total Produk: <strong class="text-dark" id="total-produk-mobile">0 produk</strong></small>
                <small class="text-muted d-block">Total: <strong class="text-dark" id="total-harga-mobile">Rp 0</strong></small>
            </div>
            <button type="submit" form="form-belanja-cepat" class="btn btn-primary fw-bold px-4 checkout-btn" style="background-color: #135291; color: white; border-radius: 10px; margin-right: 10px;">
                Checkout !!!
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===============================================
        // ============ LOGIKA UNTUK CHECKOUT ============
        // ===============================================
        const mainForm = document.getElementById('form-belanja-cepat');

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(angka);
        }

        // Fungsi untuk menghitung dan memperbarui total produk dan harga
        function calculateTotal() {
            let totalProduk = 0;
            let totalHarga = 0;

            document.querySelectorAll('.product-card.selected').forEach(card => {
                let hasValue = false; // Menandakan apakah produk ini memiliki setidaknya satu kuantitas > 0
                card.querySelectorAll('.satuan-group').forEach(group => {
                    const jumlahInput = group.querySelector('.jumlah-input');
                    const satuanSelect = group.querySelector('.satuan-select');
                    const jumlah = parseInt(jumlahInput.value) || 0; // Ambil nilai kuantitas

                    if (jumlah > 0) {
                        hasValue = true; // Produk ini memiliki kuantitas
                        const selectedOption = satuanSelect.options[satuanSelect.selectedIndex];
                        const harga = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
                        totalHarga += jumlah * harga; // Tambahkan ke total harga
                    }
                });
                if (hasValue) {
                    totalProduk++; // Hanya hitung produk jika memiliki kuantitas > 0
                }
            });

            // Perbarui tampilan total di desktop dan mobile
            document.getElementById('total-produk-desktop').textContent = `${totalProduk} produk`;
            document.getElementById('total-harga-desktop').textContent = formatRupiah(totalHarga);
            document.getElementById('total-produk-mobile').textContent = `${totalProduk} produk`;
            document.getElementById('total-harga-mobile').textContent = formatRupiah(totalHarga);
        }

        // Fungsi untuk menangani interaksi pada input kuantitas dan checkbox produk
        function handleInteraction(event) {
            const card = event.target.closest('.product-card');
            if (!card) return;

            const selector = card.querySelector('.product-selector');
            let isAnyInputFilled = false;
            // Periksa apakah ada input kuantitas yang lebih dari 0
            card.querySelectorAll('.jumlah-input').forEach(input => {
                if ((parseInt(input.value) || 0) > 0) {
                    isAnyInputFilled = true;
                }
            });

            // Set status checkbox dan kelas 'selected' pada kartu
            if (isAnyInputFilled) {
                selector.checked = true;
                card.classList.add('selected');
            } else {
                selector.checked = false;
                card.classList.remove('selected');
            }
            // Setelah interaksi, hitung ulang total
            calculateTotal();
        }

        // Fungsi untuk mengikat event pada grup satuan (input +, -, select, tambah, hapus)
        function bindSatuanGroupEvents(group) {
            const card = group.closest('.product-card'); // Dapatkan card induk

            group.addEventListener('input', handleInteraction); // Event input pada grup
            group.addEventListener('click', function(e) { // Event klik pada tombol + dan -
                const target = e.target;
                const input = target.closest('.input-group')?.querySelector('.jumlah-input');

                if (!input) return;
                let val = parseInt(input.value) || 0;
                if (target.classList.contains('btn-minus')) {
                    input.value = Math.max(0, val - 1); // Pastikan tidak kurang dari 0
                }
                if (target.classList.contains('btn-plus')) {
                    input.value = val + 1;
                }
                handleInteraction(e); // Panggil handleInteraction setelah perubahan nilai
            });

            const tambahBtn = group.querySelector('.tambah-jumlah');
            const hapusBtn = group.querySelector('.hapus-jumlah');

            // Event listener untuk tombol tambah satuan
            if (tambahBtn) {
                tambahBtn.addEventListener('click', function() {
                    const wrapper = group.closest('.jumlah-satuan-wrapper');
                    // Cek apakah select satuan saat ini sudah dipilih (tidak "--pilih--") atau unik
                    // Ini opsional, bisa dihilangkan jika Anda mengizinkan duplikasi atau default
                    // Misalnya, jika Anda tidak ingin menambah baris jika satuan yang sedang dipilih belum diisi
                    // const currentSatuanSelect = group.querySelector('.satuan-select');
                    // if (!currentSatuanSelect.value) {
                    //     alert('Pilih satuan untuk baris ini terlebih dahulu.');
                    //     return;
                    // }

                    const clone = group.cloneNode(true); // Kloning grup satuan

                    // Reset nilai input pada kloningan
                    clone.querySelector('.jumlah-input').value = '';
                    // Reset pilihan satuan ke opsi pertama (atau default)
                    clone.querySelector('.satuan-select').selectedIndex = 0;
                    // Tampilkan tombol hapus untuk grup yang baru dikloning
                    clone.querySelector('.hapus-jumlah')?.classList.remove('d-none');

                    wrapper.appendChild(clone); // Tambahkan kloningan ke DOM
                    bindSatuanGroupEvents(clone); // Ikat event untuk elemen kloningan baru

                    // Setelah menambah grup baru, perbarui total dan status seleksi
                    handleInteraction({
                        target: clone.querySelector('.jumlah-input')
                    });
                });
            }

            // Event listener untuk tombol hapus satuan
            if (hapusBtn) {
                hapusBtn.addEventListener('click', function() {
                    const wrapper = group.closest('.jumlah-satuan-wrapper');
                    const allSatuanGroups = wrapper.querySelectorAll('.satuan-group');

                    // Hanya izinkan penghapusan jika ada lebih dari satu grup satuan
                    if (allSatuanGroups.length > 1) {
                        group.remove(); // Hapus grup dari DOM
                        // Setelah menghapus grup, perbarui total dan status seleksi
                        handleInteraction({
                            target: wrapper
                        }); // Panggil dengan wrapper sebagai target
                    } else {
                        // Jika hanya satu grup tersisa, reset nilainya menjadi 0
                        const inputToReset = group.querySelector('.jumlah-input');
                        if (inputToReset) {
                            inputToReset.value = 0;
                            handleInteraction({
                                target: inputToReset
                            });
                        }
                    }
                });
            }
        }

        // Ikat event untuk semua grup satuan yang ada saat halaman dimuat
        document.querySelectorAll('.satuan-group').forEach(bindSatuanGroupEvents);

        // Event listener untuk checkbox produk utama (select/deselect)
        document.querySelectorAll('.product-selector').forEach(selector => {
            selector.addEventListener('change', function() {
                const card = this.closest('.product-card');
                if (this.checked) {
                    card.classList.add('selected');
                    // Jika dicentang, pastikan ada setidaknya 1 di input pertama
                    const firstInput = card.querySelector('.jumlah-input');
                    if (!(parseInt(firstInput.value) > 0)) {
                        firstInput.value = 1;
                    }
                } else {
                    card.classList.remove('selected');
                    // Jika tidak dicentang, kosongkan semua input kuantitas
                    card.querySelectorAll('.jumlah-input').forEach(input => input.value = '');
                    // Sembunyikan tombol hapus untuk semua grup selain yang pertama
                    const allSatuanGroups = card.querySelectorAll('.satuan-group');
                    for (let i = 1; i < allSatuanGroups.length; i++) {
                        allSatuanGroups[i].remove();
                    }
                    // Reset input pertama dan sembunyikan tombol hapus jika ada
                    const firstSatuanGroup = allSatuanGroups[0];
                    if (firstSatuanGroup) {
                        firstSatuanGroup.querySelector('.jumlah-input').value = '';
                        firstSatuanGroup.querySelector('.hapus-jumlah')?.classList.add('d-none');
                    }
                }
                calculateTotal(); // Perbarui total setelah perubahan
            });
        });

        // Event listener untuk submit form checkout
        mainForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah submit form default

            const productsToCheckout = [];
            let hasSelectedProductWithQuantity = false;

            // Kumpulkan data produk yang dipilih dengan kuantitas > 0
            document.querySelectorAll('.product-card.selected').forEach(card => {
                const produkId = card.getAttribute('data-produk-id');
                const jumlahJson = {};
                let hasValidQuantityForThisProduct = false;

                card.querySelectorAll('.satuan-group').forEach(group => {
                    const jumlah = parseInt(group.querySelector('.jumlah-input').value) || 0;
                    const satuanId = group.querySelector('.satuan-select').value;
                    if (jumlah > 0) { // Hanya tambahkan kuantitas yang > 0
                        jumlahJson[satuanId] = jumlah;
                        hasValidQuantityForThisProduct = true;
                    }
                });

                if (hasValidQuantityForThisProduct) {
                    productsToCheckout.push({
                        produk_id: produkId,
                        jumlah_json: jumlahJson
                    });
                    hasSelectedProductWithQuantity = true;
                } else {
                    // Jika card ini terpilih tapi tidak ada qty > 0, uncheck di frontend
                    const selector = card.querySelector('.product-selector');
                    if (selector) selector.checked = false;
                    card.classList.remove('selected');
                }
            });

            // Validasi di frontend: harus ada minimal satu produk dengan kuantitas
            if (!hasSelectedProductWithQuantity) {
                alert('Pilih minimal satu produk dengan jumlah lebih dari 0 sebelum melanjutkan.');
                return;
            }

            // Lakukan Validasi Stok melalui AJAX ke backend
            fetch("{{ route('mobile.form_belanja_cepat.validateCheckout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        products_to_checkout: productsToCheckout
                    })
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        // Jika validasi gagal, tampilkan alert dan kembalikan tampilan
                        alert('Checkout gagal: ' + data.message);

                        if (data.revert_data) {
                            for (const prodId in data.revert_data) {
                                const maxQuantities = data.revert_data[prodId];
                                const targetCard = document.querySelector(`.product-card[data-produk-id="${prodId}"]`);
                                if (targetCard) {
                                    // Hapus semua satuan-group yang ada kecuali yang pertama
                                    const existingSatuanGroups = targetCard.querySelectorAll('.satuan-group');
                                    for (let i = 1; i < existingSatuanGroups.length; i++) {
                                        existingSatuanGroups[i].remove();
                                    }
                                    // Reset input di satuan-group pertama
                                    const firstSatuanGroup = existingSatuanGroups[0];
                                    if (firstSatuanGroup) {
                                        firstSatuanGroup.querySelector('.jumlah-input').value = '';
                                        firstSatuanGroup.querySelector('.satuan-select').selectedIndex = 0; // Pilih opsi pertama
                                        firstSatuanGroup.querySelector('.hapus-jumlah')?.classList.add('d-none'); // Sembunyikan tombol hapus
                                    }

                                    // Iterasi dan set kuantitas maksimal yang tersedia
                                    let currentGroupIndex = 0;
                                    for (const satuanId in maxQuantities) {
                                        const qty = maxQuantities[satuanId];
                                        if (qty > 0) { // Hanya isi jika qty > 0
                                            let targetGroup;
                                            // Gunakan grup pertama jika masih kosong, atau kloning jika sudah terisi/perlu grup baru
                                            if (currentGroupIndex === 0 && firstSatuanGroup && firstSatuanGroup.querySelector('.jumlah-input').value === '') {
                                                targetGroup = firstSatuanGroup;
                                            } else {
                                                targetGroup = firstSatuanGroup.cloneNode(true);
                                                targetCard.querySelector('.jumlah-satuan-wrapper').appendChild(targetGroup);
                                                // Re-bind events untuk grup baru
                                                bindSatuanGroupEvents(targetGroup);
                                            }

                                            // Set jumlah dan satuan yang benar di grup target
                                            targetGroup.querySelector('.jumlah-input').value = qty;
                                            targetGroup.querySelector('.satuan-select').value = satuanId;
                                            targetGroup.querySelector('.hapus-jumlah')?.classList.remove('d-none');
                                            currentGroupIndex++;
                                        }
                                    }

                                    // Jika semua kuantitas menjadi 0 setelah revert, pastikan checkbox tidak dicentang
                                    let totalRevertedQty = 0;
                                    for (const sId in maxQuantities) {
                                        totalRevertedQty += maxQuantities[sId];
                                    }
                                    if (totalRevertedQty === 0) {
                                        targetCard.classList.remove('selected');
                                        const selector = targetCard.querySelector('.product-selector');
                                        if (selector) selector.checked = false;
                                    } else {
                                        // Jika ada kuantitas > 0 setelah revert, pastikan card selected
                                        targetCard.classList.add('selected');
                                        const selector = targetCard.querySelector('.product-selector');
                                        if (selector) selector.checked = true;
                                    }
                                }
                            }
                        }
                        // Setelah nilai dikembalikan, hitung ulang total
                        calculateTotal();
                    } else {
                        // Jika validasi sukses, kumpulkan data untuk submit form utama
                        const dataContainer = document.getElementById('form-data-container');
                        dataContainer.innerHTML = ''; // Bersihkan kontainer

                        // Tambahkan input hidden untuk data produk yang akan dikirim ke halaman konfirmasi
                        productsToCheckout.forEach((productData, index) => {
                            const produkIdInput = document.createElement('input');
                            produkIdInput.type = 'hidden';
                            produkIdInput.name = `produk_data[${index}][produk_id]`;
                            produkIdInput.value = productData.produk_id;
                            dataContainer.appendChild(produkIdInput);

                            for (const [satuanId, qty] of Object.entries(productData.jumlah_json)) {
                                const jumlahInput = document.createElement('input');
                                jumlahInput.type = 'hidden';
                                jumlahInput.name = `produk_data[${index}][jumlah_json][${satuanId}]`;
                                jumlahInput.value = qty;
                                dataContainer.appendChild(jumlahInput);
                            }
                        });

                        mainForm.submit(); // Lanjutkan submit form yang sebenarnya
                    }
                })
                .catch(error => {
                    console.error('Error saat validasi checkout:', error);
                    alert('Terjadi kesalahan saat melakukan validasi checkout. Silakan coba lagi.');
                });
        });

        // ===============================================
        // ===== LOGIKA UNTUK FILTER DAN LIVE SEARCH =====
        // ===============================================
        const searchInput = document.querySelector('#filter-form input[name="search"]');
        const filterForm = document.getElementById('filter-form');
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (searchInput.value.trim() !== '' || filterForm.querySelector('select[name="kategori"]').value !== '') {
                    filterForm.submit();
                }
            }, 500);
        });

        filterForm.addEventListener('submit', function(e) {
            // Jika search input kosong dan kategori juga kosong, cegah submit
            if (searchInput.value.trim() === '' && filterForm.querySelector('select[name="kategori"]').value === '') {
                e.preventDefault();
                // Arahkan ke URL bersih untuk menghapus parameter query lama
                window.location.href = "{{ route('mobile.form_belanja_cepat.index') }}";
            }
        });

        // Panggil inisialisasi pada load DOM untuk mengeset status terpilih
        // dan menghitung total berdasarkan kondisi awal
        document.querySelectorAll('.product-card').forEach(card => {
            handleInteraction({
                target: card.querySelector('.jumlah-input') || card.querySelector('.product-selector')
            });
        });
        calculateTotal();
    });
</script>
@endpush