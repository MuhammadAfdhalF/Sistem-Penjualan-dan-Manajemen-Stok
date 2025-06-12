@extends('layouts.template_mobile')
@section('title', 'Halaman Proses Transaksi - KZ Family')

@push('head')
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



    @media (max-width: 576px) {
        main.main-content {
            margin-bottom: 0px;
            padding-bottom: 0px;
        }

        .footer-mobile-nav {
            display: none !important;
        }

        .card.rincian-belanja {
            font-size: 0.75rem;
            /* kecil tapi masih terbaca jelas */
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
            height: 80%;

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
            height: 35px !important;
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
            font-size: 0.95rem !important;
            padding: 0.5rem 0.6rem !important;
            height: 40px !important;
            width: 45% !important;
            border-radius: 10px;
            text-align: center;
        }
    }

    @media (max-width: 1000px) and (orientation: landscape) {
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
<form action="{{ route('mobile.proses_transaksi.store') }}" method="POST">
    @csrf

    @foreach(request('keranjang_id', []) as $id)
    <input type="hidden" name="keranjang_id[]" value="{{ $id }}">
    @endforeach

    <input type="hidden" name="metode_pengambilan" id="metode_pengambilan">
    <input type="hidden" name="metode_pembayaran" id="metode_pembayaran">

    <div class="container-fluid px-0 px-md-4 py-2" style="max-width: 1280px;">

        {{-- HEADER --}}
        <div class="bg-white shadow-sm mb-1 d-block d-lg-none header-transaksi-mobile">
            {{-- Bagian panah kembali --}}
            <div class="px-3 py-2">
                <i class="bi bi-arrow-left fs-3" style="cursor:pointer;"></i>
            </div>

            {{-- Pemisah halus antara ikon dan teks --}}
            <div style="height: 1px; background: rgba(0, 0, 0, 0.19); margin: 0;"></div>

            {{-- Bagian teks header --}}
            <div class="px-3 pb-3 pt-2 bg-white">
                <div class="fw-bold">Toko KZ Family</div>
                <div class="text-muted">Proses Transaksi</div>
            </div>
        </div>




        {{-- RINCIAN BELANJA --}}
        {{-- RINCIAN BELANJA --}}
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
                @foreach($keranjangs as $item)
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
                        $qty = $item->jumlah_json[$satuan->id] ?? 0;
                        $harga = $produk->hargaProduks->firstWhere('satuan_id', $satuan->id)?->harga ?? 0;
                        @endphp
                        @if($qty > 0)
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
                @endforeach

                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total Harga</span>
                    <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- METODE PENGAMBILAN --}}
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

        {{-- METODE PEMBAYARAN --}}
        <div class="card mb-1 shadow metode-pembayaran">
            <div class="card-header fw-bold bg-white"><i class="bi bi-wallet2 me-2"></i>Metode Pembayaran</div>
            <div class="card-body">
                <p class="text-muted mb-2">Pilih metode pembayaran</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn w-45 text-white btn-pembayaran" data-value="bayar_di_toko" style="background-color: #058DA9;">Cash</button>
                    <button type="button" class="btn w-45 text-white btn-pembayaran" data-value="payment_gateway" style="background-color: #0057B2;">Digital</button>
                </div>
            </div>
        </div>


        {{-- ALAMAT PENGIRIMAN --}}
        <div class="card mb-1 shadow card-alamat" id="alamat-card">
            <div class="card-body px-3 pt-3 pb-2">
                <label class="form-label fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>Alamat Pengiriman</label>
                <textarea class="form-control py-2" rows="3" name="alamat_pengambilan" placeholder="Masukkan alamat pengiriman"></textarea>
            </div>
        </div>

        {{-- CATATAN --}}
        <div class="card mb-4 shadow card-catatan">
            <div class="card-body px-3 pt-3 pb-2">
                <label class="form-label fw-bold"><i class="bi bi-journal-text me-2"></i>Catatan</label>
                <textarea class="form-control py-2" rows="3" name="catatan" placeholder="Tambahkan catatan..."></textarea>
            </div>
        </div>

        {{-- TOMBOL --}}
        <div class="d-flex gap-2 mb-2 justify-content-center mobile-btn-container fixed-bottom-aksi">
            <a href="{{ route('mobile.keranjang.index') }}" class="btn w-45 text-white mobile-btn-small" style="background:#c62828;">Batalkan</a>
            <button type="submit" class="btn w-45 text-white mobile-btn-small" style="background:#0d47a1;">Buat Pesanan</button>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
    const alamatCard = document.querySelector('.card-alamat');

    // Handle tombol pengambilan
    document.querySelectorAll('.btn-pengambilan').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('metode_pengambilan').value = this.dataset.value;
            document.querySelectorAll('.btn-pengambilan').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const alamatCard = document.getElementById('alamat-card');
            if (this.dataset.value === 'ambil di toko') {
                alamatCard.style.display = 'none';
            } else {
                alamatCard.style.display = 'block';
            }
        });
    });

    // Handle tombol pembayaran
    document.querySelectorAll('.btn-pembayaran').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('metode_pembayaran').value = this.dataset.value;
            document.querySelectorAll('.btn-pembayaran').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Validasi sebelum submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const metodePengambilan = document.getElementById('metode_pengambilan').value;
        const metodePembayaran = document.getElementById('metode_pembayaran').value;

        if (!metodePengambilan || !metodePembayaran) {
            e.preventDefault();
            alert('Pilih metode pengambilan dan metode pembayaran terlebih dahulu.');
        }
    });
</script>
@endpush