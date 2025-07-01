@extends('layouts.template_mobile')
@section('title', 'Halaman Proses Transaksi - KZ Family')

@push('head')
<style>
    main.main-content {
        margin-bottom: 0px;
        padding-bottom: 0px;
    }


    .w-45 {
        width: 45% !important;

    }

    .btn-pengambilan.active,
    .btn-pembayaran.active {
        border: 2px solid #fff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5);
        transform: scale(0.97);
        opacity: 1;
    }

    .btn-pengambilan,
    .btn-pembayaran,
    .btn-aksi {
        transition: all 0.2s ease-in-out;
        border-radius: 12px;
        font-size: 1rem;
        padding: 0.6rem 1rem;
    }

    .btn-aksi:active {
        transform: scale(0.96);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .card {
        border-radius: 16px;
    }

    textarea.form-control {
        font-size: 0.95rem;
        border-radius: 12px;
    }

    /* 🔥 Tambahan: Atur agar tombol pembayaran yang disembunyikan tidak memakan tempat */
    .btn-pembayaran[style*="display: none"] {
        display: none !important;
    }


    @media (max-width: 576px) {
        main.main-content {
            margin-bottom: 0px;
            padding-bottom: 0px;
        }

        .card.metode-pembayaran .btn {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.7rem !important;
            height: auto !important;
            border-radius: 12px;
        }


        .footer-mobile-nav {
            display: none !important;
        }

        .card.rincian-belanja {
            font-size: 0.75rem;
            line-height: 1.4;
        }

        .card.rincian-belanja .fw-bold {
            font-size: 0.8rem;
        }

        .card.rincian-belanja .text-end {
            font-size: 0.75rem;
        }

        .header-transaksi-mobile .fw-bold {
            font-size: 0.8rem !important;
        }

        .header-transaksi-mobile .text-muted {
            font-size: 0.75rem !important;
        }

        .header-transaksi-mobile .bi {
            font-size: 1.3rem !important;
        }

        .w-45 {
            width: 30% !important;
            height: auto;
        }

        .card.metode-pengambilan,
        .card.metode-pembayaran {
            font-size: 0.75rem;
            line-height: 1.4;
        }

        .card.metode-pengambilan .fw-bold,
        .card.metode-pembayaran .fw-bold {
            font-size: 0.8rem;
        }

        .card.metode-pengambilan .btn,
        .card.metode-pembayaran .btn {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.7rem !important;
            height: auto !important;
        }

        .card.metode-pengambilan p,
        .card.metode-pembayaran p {
            font-size: 0.75rem;
        }

        .card-alamat,
        .card-catatan {
            font-size: 0.75rem;
            line-height: 1.4;
        }

        .card-alamat .form-label,
        .card-catatan .form-label {
            font-size: 0.8rem;
        }

        .card-alamat textarea,
        .card-catatan textarea {
            font-size: 0.75rem;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
        }

        .mobile-btn-container {
            justify-content: center !important;
        }

        .mobile-btn-small {
            font-size: 0.85rem !important;
            padding: 0.5rem 0.6rem !important;
            height: 40px !important;
            width: 45% !important;
            border-radius: 10px;
            text-align: center;
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
</style>
@endpush
@section('content')

<form action="{{ (isset($from_form_cepat) && $from_form_cepat) ? route('mobile.form_belanja_cepat.store') : route('mobile.proses_transaksi.store') }}" method="POST" class="fs-6">
    @csrf

    <input type="hidden" name="metode_pengambilan" id="metode_pengambilan" value="{{ old('metode_pengambilan') }}">
    <input type="hidden" name="metode_pembayaran" id="metode_pembayaran" value="{{ old('metode_pembayaran') }}">

    <div class="container-fluid px-0 px-md-4 py-2" style="max-width: 1280px;">
        <div class="bg-white shadow-sm mb-1 d-block d-lg-none header-transaksi-mobile">
            <div class="px-3 py-2">
                <a href="javascript:history.back()" class="text-dark"><i class="bi bi-arrow-left fs-3" style="cursor:pointer;"></i></a>
            </div>
            <div style="height: 1px; background: rgba(0, 0, 0, 0.19); margin: 0;"></div>
            <div class="px-3 pb-3 pt-2 bg-white">
                <div class="fw-bold">Toko KZ Family</div>
                <div class="text-muted">Proses Transaksi</div>
            </div>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger mx-3">
            <h6 class="fw-bold">Terjadi Kesalahan:</h6>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        @if (session('error'))
        <div class="alert alert-danger mx-3">
            {{ session('error') }}
        </div>
        @endif

        <div class="card mb-1 shadow rounded-4 rincian-belanja">
            <div class="card-header fw-bold bg-white"><i class="bi bi-receipt me-2"></i>Rincian Belanja</div>
            <div class="card-body">
                <div class="row fw-semibold border-bottom pb-2">
                    <div class="col-4">Nama Produk</div>
                    <div class="col-2">Satuan</div>
                    <div class="col-3 text-end">Harga</div>
                    <div class="col-3 text-end">Sub Total</div>
                </div>
                @php $total = 0; @endphp
                @forelse($keranjangs as $item)
                @if($item->produk)
                @php
                $produk = $item->produk;
                $satuans = $produk->satuans()->orderByDesc('konversi_ke_satuan_utama')->get();
                @endphp
                <div class="row mt-3">
                    <div class="col-4">{{ $produk->nama_produk }}</div>
                    <div class="col-2">
                        @foreach($satuans as $satuan)
                        @php $qty = $item->jumlah_json[$satuan->id] ?? 0; @endphp
                        @if($qty > 0)
                        {{ $qty }} x {{ $satuan->nama_satuan }}<br>
                        @endif
                        @endforeach
                    </div>
                    <div class="col-3 text-end">
                        @foreach($satuans as $satuan)
                        @php
                        $harga = $produk->hargaProduks->firstWhere('satuan_id', $satuan->id)?->harga ?? 0;
                        @endphp
                        @if(($item->jumlah_json[$satuan->id] ?? 0) > 0)
                        Rp {{ number_format($harga, 0, ',', '.') }}<br>
                        @endif
                        @endforeach
                    </div>
                    <div class="col-3 text-end">
                        @foreach($satuans as $satuan)
                        @php
                        $qty = $item->jumlah_json[$satuan->id] ?? 0;
                        $harga = $produk->hargaProduks->firstWhere('satuan_id', $satuan->id)?->harga ?? 0;
                        $subtotal = $harga * $qty;
                        $total += $subtotal;
                        @endphp
                        @if($qty > 0)
                        Rp {{ number_format($subtotal, 0, ',', '.') }}<br>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @empty
                <div class="text-center text-muted py-3">Tidak ada produk untuk diproses.</div>
                @endforelse

                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total Harga</span>
                    <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="card mb-1 shadow metode-pengambilan">
            <div class="card-header fw-bold bg-white"><i class="bi bi-truck me-2"></i>Metode Pengambilan</div>
            <div class="card-body">
                <p class="text-muted mb-2">Pilih metode pengambilan barang</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn w-45 text-white btn-pengambilan" data-value="ambil di toko" style="background-color: #058DA9;">Di Toko</button>
                    <button type="button" class="btn w-45 text-white btn-pengambilan" data-value="diantar" style="background-color: #0057B2;">Diantar</button>
                </div>
            </div>
        </div>

        <div class="card mb-1 shadow metode-pembayaran">
            <div class="card-header fw-bold bg-white"><i class="bi bi-wallet2 me-2"></i>Metode Pembayaran</div>
            <div class="card-body">
                <p class="text-muted mb-2">Pilih metode pembayaran</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn w-45 text-white btn-pembayaran" data-value="cod"
                        style="background-color:rgb(101, 149, 168);">COD</button>
                    <button type="button" class="btn w-45 text-white btn-pembayaran" data-value="bayar_di_toko"
                        style="background-color: #058DA9;">Bayar di Toko</button>
                    <button type="button" class="btn w-45 text-white btn-pembayaran" data-value="payment_gateway"
                        style="background-color: #0057B2;">Digital</button>
                </div>

            </div>
        </div>


        <div class="card mb-1 shadow card-alamat" id="alamat-card" style="display: none;">
            <div class="card-body px-3 pt-3 pb-2">
                <label class="form-label fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>Alamat Pengiriman</label>
                <textarea class="form-control py-2 @error('alamat_pengambilan') is-invalid @enderror" rows="3" name="alamat_pengambilan" placeholder="Masukkan alamat pengiriman">{{ old('alamat_pengambilan', Auth::user()->alamat) }}</textarea>
            </div>
        </div>

        <div class="card mb-4 shadow card-catatan">
            <div class="card-body px-3 pt-3 pb-2">
                <label class="form-label fw-bold"><i class="bi bi-journal-text me-2"></i>Catatan</label>
                <textarea class="form-control py-2" rows="3" name="catatan" placeholder="Tambahkan catatan...">{{ old('catatan') }}</textarea>
            </div>
        </div>

        <div class="d-flex gap-2 mb-2 justify-content-center mobile-btn-container fixed-bottom-aksi">
            <a href="javascript:history.back()" class="btn w-45 text-white mobile-btn-small" style="background:#c62828;">Batalkan</a>
            <button type="submit" class="btn w-45 text-white mobile-btn-small" style="background:#0d47a1;">Buat Pesanan</button>
        </div>

    </div>
</form>
@endsection


@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alamatCard = document.getElementById('alamat-card');
        const hiddenPengambilan = document.getElementById('metode_pengambilan');
        const hiddenPembayaran = document.getElementById('metode_pembayaran');
        const form = document.querySelector('form');

        const btnCod = document.querySelector(".btn-pembayaran[data-value='cod']");
        const btnBayarDiToko = document.querySelector(".btn-pembayaran[data-value='bayar_di_toko']");
        const btnDigital = document.querySelector(".btn-pembayaran[data-value='payment_gateway']");

        function initializeButtons() {
            const oldPengambilan = hiddenPengambilan.value;
            if (oldPengambilan) {
                document.querySelector(`.btn-pengambilan[data-value='${oldPengambilan}']`)?.click();
            }
            const oldPembayaran = hiddenPembayaran.value;
            if (oldPembayaran) {
                document.querySelector(`.btn-pembayaran[data-value='${oldPembayaran}']`)?.click();
            }
        }

        initializeButtons();

        document.querySelectorAll('.btn-pengambilan').forEach(btn => {
            btn.addEventListener('click', function() {
                hiddenPengambilan.value = this.dataset.value;
                document.querySelectorAll('.btn-pengambilan').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                alamatCard.style.display = this.dataset.value === 'diantar' ? 'block' : 'none';

                const pembayaranTerpilih = hiddenPembayaran.value;
                if (this.dataset.value === 'diantar') {
                    btnBayarDiToko.style.display = 'none';
                    btnCod.style.display = 'block';
                    if (pembayaranTerpilih === 'bayar_di_toko') {
                        hiddenPembayaran.value = '';
                        btnBayarDiToko.classList.remove('active');
                    }
                } else {
                    btnBayarDiToko.style.display = 'block';
                    btnCod.style.display = 'none';
                    if (pembayaranTerpilih === 'cod') {
                        hiddenPembayaran.value = '';
                        btnCod.classList.remove('active');
                    }
                }
            });
        });

        document.querySelectorAll('.btn-pembayaran').forEach(btn => {
            btn.addEventListener('click', function() {
                hiddenPembayaran.value = this.dataset.value;
                document.querySelectorAll('.btn-pembayaran').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        form.addEventListener('submit', async function(e) {
            const metodePengambilan = hiddenPengambilan.value;
            const metodePembayaran = hiddenPembayaran.value;
            const alamatPengambilan = document.querySelector('textarea[name="alamat_pengambilan"]')?.value || '';

            if (!metodePengambilan || !metodePembayaran) {
                e.preventDefault();
                alert('Pilih metode pengambilan dan metode pembayaran terlebih dahulu.');
                return;
            }

            if (metodePengambilan === 'diantar' && !alamatPengambilan.trim()) {
                e.preventDefault();
                alert('Alamat pengiriman harus diisi jika memilih metode diantar.');
                return;
            }

            // Jika metode pembayaran adalah payment_gateway, lakukan AJAX call untuk mendapatkan Snap Token
            if (metodePembayaran === 'payment_gateway') {
                e.preventDefault(); // stop default submit

                const formData = new FormData(form);

                // Ambil total dari rincian belanja di view
                const totalElement = document.querySelector('.card.rincian-belanja .d-flex.justify-content-between.fw-bold span:last-child');
                const totalText = totalElement ? totalElement.textContent.replace('Rp ', '').replace(/\./g, '') : '0';
                const total = parseFloat(totalText);

                // Ambil data produk dari rincian belanja untuk item_details
                const itemDetails = [];
                document.querySelectorAll('.card.rincian-belanja .row.mt-3').forEach(row => {
                    const productName = row.querySelector('.col-4').textContent.trim();
                    const productDetails = row.querySelector('.col-2'); // Ini berisi qty x satuan
                    const priceDetails = row.querySelector('.col-3:nth-child(3)'); // Ini berisi harga per satuan

                    // Mengurai jumlah dan harga dari HTML
                    const quantitiesHtml = productDetails.innerHTML.split('<br>').filter(Boolean);
                    const pricesHtml = priceDetails.innerHTML.split('<br>').filter(Boolean);

                    quantitiesHtml.forEach((qtyHtml, index) => {
                        const qtyMatch = qtyHtml.match(/(\d+) x (.+)/);
                        const priceMatch = pricesHtml[index] ? pricesHtml[index].match(/Rp ([\d\.]+)/) : null;

                        if (qtyMatch && priceMatch) {
                            const quantity = parseInt(qtyMatch[1]);
                            const unitName = qtyMatch[2].trim();
                            const price = parseFloat(priceMatch[1].replace(/\./g, '')); // Hapus titik format ribuan

                            // Anda mungkin perlu ID produk dan satuan yang sebenarnya di sini.
                            // Untuk saat ini, kita akan menggunakan ID dummy atau string unik.
                            // Idealnya, Anda akan memiliki data-attributes di HTML yang menyimpan ID produk/satuan.
                            // Karena tidak ada data-attribute di HTML saat ini, kita akan pakai nama produk + satuan sebagai ID unik.
                            const itemId = `PROD-${productName.replace(/\s/g, '-')}-${unitName.replace(/\s/g, '-')}`;

                            itemDetails.push({
                                id: itemId,
                                price: price,
                                quantity: quantity,
                                name: `${productName} (${unitName})`
                            });
                        }
                    });
                });

                // Kirim request ke ProsesTransaksiController@store/formBelanjaCepatStore
                // Controller akan membuat transaksi dan mengembalikan snap_token + order_id
                try {
                    // 🔥 Tambahkan item_details ke formData sebagai string JSON
                    // Pastikan nama field di backend adalah 'item_details'
                    formData.append('item_details', JSON.stringify(itemDetails));
                    formData.append('total', total); // Pastikan total juga dikirim

                    const response = await fetch(form.action, {
                        method: "POST",
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    });

                    const result = await response.json();

                    if (result.snap_token) {
                        window.snap.pay(result.snap_token, {
                            onSuccess: function(result) {
                                window.location.href = "/mobile/pesanan/sukses";
                            },
                            onPending: function(result) {
                                window.location.href = "/mobile/pesanan/menunggu";
                            },
                            onError: function(result) {
                                alert("Terjadi kesalahan saat memproses pembayaran.");
                            },
                            onClose: function() {
                                alert('Pembayaran dibatalkan.');
                            }
                        });
                    } else {
                        alert(result.error || 'Gagal mendapatkan Snap Token.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Gagal memproses pembayaran.');
                }
            }
        });
    });
</script>

@endpush