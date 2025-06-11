@extends('layouts.template_mobile')
@section('title', 'Halaman Proses Transaksi - KZ Family')

@push('head')
<style>
    /* Responsive mobile */
    @media (max-width: 576px) {
        .footer-mobile-nav {
            display: none !important;
        }

        .rincian-header .col-4,
        .rincian-header .col-2,
        .rincian-header .col-3 {
            font-size: 0.8rem;
        }

        .rincian-body .col-4,
        .rincian-body .col-2,
        .rincian-body .col-3 {
            font-size: 0.82rem;
        }

        .rincian-body .col-2 {
            padding-left: 0.4rem;
        }

        .rincian-body .row {
            margin-bottom: 0.8rem;
        }

        .mobile-text-small {
            font-size: 0.85rem !important;
            line-height: 1.3;
        }

        .mobile-button-slim {
            width: 120px !important;
            /* Atur lebar pasti */
            height: 35px;
            /* Tinggi tombol */
            padding: 0.5rem 1rem;
            /* Padding atas/bawah dan kanan/kiri */
            font-size: 0.85rem;
            border-radius: 8px;
            text-align: center;
        }

        .mobile-justify-center {
            justify-content: center !important;
            gap: 1rem;
        }

        .mobile-btn-small {
            font-size: 0.82rem !important;
            padding: 0.4rem 0.5rem !important;
            height: 40px !important;
            width: 180px !important;
        }

        .mobile-btn-container {
            justify-content: center !important;
        }

        .mobile-fs-xs {
            font-size: 0.85rem !important;
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
    <input type="hidden" name="metode_pengambilan" id="metode_pengambilan" value="ambil di toko">
    <input type="hidden" name="metode_pembayaran" id="metode_pembayaran" value="bayar_di_toko">

    <div class="container-fluid px-0 px-md-4 py-2" style="max-width: 1280px;">

        <!-- Header Navigasi -->
        <div class="bg-white shadow-sm mb-1 d-block d-lg-none">
            <div class="px-3 py-2" style="box-shadow: 0 2px 4px rgba(0, 0, 0, 0.36); z-index:1; position:relative;">
                <i class="bi bi-arrow-left fs-3" style="cursor:pointer;"></i>
            </div>
            <div class="px-3 pb-3 pt-2 bg-white" style="z-index:0; position:relative; box-shadow: 0 4px 6px -2px rgba(0, 0, 0, 0.1);">
                <div class="fw-bold fs-6 fs-md-5">Toko KZ Family</div>
                <div class="text-muted mobile-fs-xs fs-md-6">Proses Transaksi</div>
            </div>
        </div>

        <!-- Rincian Belanja -->
        <div class="card mb-1 shadow" style="border-radius: 8px;">
            <div class="card-header fw-bold bg-white fs-6 fs-md-5">
                <i class="bi bi-receipt me-2"></i>Rincian Belanja
            </div>
            <div class="card-body fs-8 fs-md-6">
                <div class="row fw-semibold border-bottom pb-2 rincian-header">
                    <div class="col-4">Nama Produk</div>
                    <div class="col-2">Satuan</div>
                    <div class="col-3 text-end">Harga</div>
                    <div class="col-3 text-end">Sub Total</div>
                </div>

                @php $total = 0; @endphp
                @foreach($keranjangs as $item)
                @php
                $namaProduk = $item->produk->nama_produk;
                $satuans = $item->produk->satuans()->orderByDesc('konversi_ke_satuan_utama')->get();
                @endphp

                <div class="row mt-3 rincian-body">
                    <div class="col-4 fw-medium">{{ $namaProduk }}</div>
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
                        $harga = $item->produk->hargaProduks->firstWhere('satuan_id', $satuan->id)?->harga ?? 0;
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
                        $harga = $item->produk->hargaProduks->firstWhere('satuan_id', $satuan->id)?->harga ?? 0;
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
                <div class="d-flex justify-content-between align-items-center fw-bold">
                    <span><i class="bi bi-cash-stack me-2"></i>Total Harga</span>
                    <span class="fs-6 fs-md-5">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Metode Pengambilan -->
        <div class="card mb-1 shadow" style="border-radius: 8px;">
            <div class="card-header fw-bold bg-white fs-6 fs-md-5">
                <i class="bi bi-truck me-2"></i>Metode Pengambilan
            </div>
            <div class="card-body fs-8 fs-md-6">
                <p class="text-muted mb-2 fs-8 fs-md-5 fs-lg-4 mobile-text-small">
                    Pengambilan barang bisa diambil di toko dan diantar ke alamat
                </p>
                <div class="d-flex gap-2 mobile-justify-center">
                    <button type="button" class="btn w-50 text-white mobile-button-slim btn-pengambilan" data-value="ambil di toko" style="background-color: #058DA9;">Di Toko</button>
                    <button type="button" class="btn w-50 text-white mobile-button-slim btn-pengambilan" data-value="diantar" style="background-color: #0057B2;">Diantar</button>
                </div>
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="card mb-1 shadow" style="border-radius: 8px;">
            <div class="card-header fw-bold bg-white fs-6 fs-md-5">
                <i class="bi bi-wallet2 me-2"></i>Metode Pembayaran
            </div>
            <div class="card-body fs-8 fs-md-6">
                <p class="text-muted mb-2 fs-8 fs-md-5 fs-lg-4 mobile-text-small">
                    Pembayaran untuk ditempat pilih cash dan untuk pembayaran online pilih pembayaran digital
                </p>
                <div class="d-flex gap-2 mobile-justify-center">
                    <button type="button" class="btn w-50 text-white mobile-button-slim btn-pembayaran" data-value="bayar_di_toko" style="background-color: #058DA9;">Cash</button>
                    <button type="button" class="btn w-50 text-white mobile-button-slim btn-pembayaran" data-value="payment_gateway" style="background-color: #0057B2;">Digital</button>
                </div>
            </div>
        </div>

        <!-- Alamat Pengiriman -->
        <div class="card mb-1 shadow">
            <div class="card-body px-3 pt-3 pb-2">
                <label class="form-label fw-bold fs-6 fs-md-5 mb-1">
                    <i class="bi bi-geo-alt-fill me-2"></i>Alamat Pengiriman
                </label>
                <textarea class="form-control fs-8 fs-md-5 fs-lg-4 mobile-text-small py-2" rows="3" name="alamat_pengambilan" placeholder="Masukkan alamat pengiriman"></textarea>
            </div>
        </div>

        <!-- Catatan -->
        <div class="card mb-4 shadow">
            <div class="card-body px-3 pt-3 pb-2">
                <label class="form-label fw-bold fs-6 fs-md-5 mb-1">
                    <i class="bi bi-journal-text me-2"></i>Catatan
                </label>
                <textarea class="form-control fs-8 fs-md-5 fs-lg-4 mobile-text-small py-2" rows="3" name="catatan" placeholder="Tambahkan catatan..."></textarea>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="d-flex gap-2 mb-5 mobile-btn-container">
            <a href="{{ route('mobile.keranjang.index') }}" class="btn w-50 mobile-btn-small" style="background:#c62828; color:#fff; font-weight:600;">Batalkan</a>
            <button type="submit" class="btn w-50 mobile-btn-small" style="background:#0d47a1; color:#fff; font-weight:600;">Buat Pesanan</button>
        </div>

    </div>
</form>

@push('scripts')
<script>
    document.querySelectorAll('.btn-pengambilan').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('metode_pengambilan').value = this.dataset.value;
            document.querySelectorAll('.btn-pengambilan').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    document.querySelectorAll('.btn-pembayaran').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('metode_pembayaran').value = this.dataset.value;
            document.querySelectorAll('.btn-pembayaran').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>
@endpush
@endsection