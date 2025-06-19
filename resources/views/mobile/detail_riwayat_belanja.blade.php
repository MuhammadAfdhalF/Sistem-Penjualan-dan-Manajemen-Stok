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



    .btn-metode-pengambilan,
    .btn-metode-pembayaran,
    .btn-aksi {
        transition: all 0.2s ease-in-out;
        border-radius: 12px;
        font-size: 1rem;
        padding: 0.6rem 1rem;
    }



    .card {
        border-radius: 16px;
    }

    textarea.form-control {
        font-size: 0.95rem;
        border-radius: 12px;
    }

    .btn-pembayaran[style*="display: none"] {
        display: none !important;
    }

    @media (max-width: 576px) {

        .footer-mobile-nav {
            display: none !important;
        }

        .card.status-transaksi {
            font-size: 0.75rem;
            line-height: 1.4;
        }

        .card.status-transaksi .fw-semibold {
            font-size: 0.8rem;
        }

        .card.status-transaksi .badge {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
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

        .card-alamat textarea,
        .card-catatan textarea {
            font-size: 0.75rem;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
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
<form>
    <input type="hidden" name="metode_pengambilan" id="metode_pengambilan" value="{{ $transaksi->metode_pengambilan }}">
    <input type="hidden" name="metode_pembayaran" id="metode_pembayaran" value="{{ $transaksi->metode_pembayaran }}">

    <div class="container-fluid px-0 px-md-4 py-2" style="max-width: 1280px;">
        <div class="bg-white shadow-sm mb-1 d-block d-lg-none header-transaksi-mobile">
            <div class="px-3 py-2">
                <a href="#" onclick="history.back()" class="text-dark">
                    <i class="bi bi-arrow-left" style="font-size: 1.5rem;"></i>
                </a>
            </div>
            <div style="height: 1px; background: rgba(0, 0, 0, 0.19);"></div>
            <div class="px-3 pb-3 pt-2 bg-white">
                <div class="fw-semibold" style="font-size: 0.95rem;">Toko KZ Family</div>
                <div class="text-muted" style="font-size: 0.8rem;">Detail Riwayat</div>
            </div>
        </div>
    </div>


    <!-- STATUS TRANSAKSI & PEMBAYARAN  -->
    <div class="card status-transaksi shadow-sm mb-2 px-3 py-3" style="border-radius: 12px;">
        @php
        $isOnline = $tipe === 'online';
        $statusTransaksi = strtolower($transaksi->status_transaksi ?? 'menunggu');
        $statusPembayaran = strtolower($transaksi->status_pembayaran ?? 'pending');

        $statusPesananText = $isOnline ? 'Online - ' . ucfirst($statusTransaksi) : 'Offline';
        $warnaStatus = $isOnline ? ($statusTransaksi === 'selesai' ? 'green' : 'orange') : 'grey';

        $statusPembayaranText = $isOnline ? ucwords($statusPembayaran) : 'Lunas';
        $warnaPembayaran = $isOnline
        ? ($statusPembayaran === 'lunas' ? 'green' : ($statusPembayaran === 'gagal' ? 'red' : 'orange'))
        : 'green';
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold" style="font-size: 0.9rem;">Status Pesanan</div>
            <div>
                <span class="badge rounded-pill text-white"
                    style="background-color: {{ $warnaStatus }};
                         font-size: 0.75rem;
                         padding: 4px 10px;">
                    {{ $statusPesananText }}
                </span>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold" style="font-size: 0.9rem;">Status Pembayaran</div>
            <div>
                <span class="badge rounded-pill text-white"
                    style="background-color: {{ $warnaPembayaran }};
                         font-size: 0.75rem;
                         padding: 4px 10px;">
                    {{ $statusPembayaranText }}
                </span>
            </div>
        </div>
    </div>


    <!-- RINCIAN BELANJA -->
    <div class="card mb-1 shadow rincian-belanja">
        <div class="card-header fw-bold bg-white"><i class="bi bi-receipt me-2"></i>Rincian Belanja</div>
        <div class="card-body">
            <div class="row fw-semibold border-bottom pb-2">
                <div class="col-4">Nama Produk</div>
                <div class="col-3">Satuan</div>
                <div class="col-2 text-end">Harga</div>
                <div class="col-3 text-end">Sub Total</div>
            </div>

            @foreach($transaksi->detail as $detail)
            @php
            $produk = $detail->produk;
            $satuans = $produk->satuans;
            $jumlahPerSatuan = $detail->jumlah_json ?? [];
            $hargaPerSatuan = $detail->harga_json ?? [];
            $isFirstRow = true;
            $rowPrinted = false;
            @endphp

            @foreach($jumlahPerSatuan as $satuanId => $qty)
            @php
            $qty = floatval($qty);
            if ($qty <= 0) continue;

                $satuan=$satuans->firstWhere('id', (int)$satuanId);
                $namaSatuan = $satuan->nama_satuan ?? 'unit';
                $harga = floatval($hargaPerSatuan[$satuanId] ?? 0);
                $subtotal = $qty * $harga;
                $rowClass = $rowPrinted ? '' : 'mt-3'; // jarak hanya baris pertama produk
                $rowPrinted = true;
                @endphp
                <div class="row {{ $rowClass }}">
                    <div class="col-4">
                        @if($isFirstRow)
                        {{ $produk->nama_produk }}
                        @php $isFirstRow = false; @endphp
                        @endif
                    </div>
                    <div class="col-3">{{ $qty }} x {{ $namaSatuan }}</div>
                    <div class="col-2 text-end">Rp {{ number_format($harga, 0, ',', '.') }}</div>
                    <div class="col-3 text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
                </div>
                @endforeach
                @endforeach

                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total Harga</span>
                    <span>Rp {{ number_format($total ?? $transaksi->detail->sum('subtotal'), 0, ',', '.') }}</span>
                </div>
        </div>
    </div>
    <!-- METODE PEMBAYARAN -->
    <div class="card mb-1 shadow metode-pembayaran">
        <div class="card-header fw-bold bg-white"><i class="bi bi-wallet2 me-2"></i>Metode Pembayaran</div>
        <div class="card-body">
            <p class="text-muted mb-2">Metode pembayaran</p>
            <div class="d-flex justify-content-center gap-2">
                @if($transaksi->metode_pembayaran === 'cod')
                <span class="btn-metode-pembayaran btn w-75 text-white active" style="background-color: rgb(101, 149, 168);">COD</span>
                @elseif($transaksi->metode_pembayaran === 'bayar_di_toko')
                <span class="btn-metode-pembayaran btn w-75 text-white active" style="background-color: #058DA9;">Bayar di Toko</span>
                @elseif($transaksi->metode_pembayaran === 'payment_gateway')
                <span class="btn-metode-pembayaran btn w-75 text-white active" style="background-color: #0057B2;">Digital</span>
                @endif
            </div>
        </div>
    </div>
    @if ($tipe === 'online')
    <!-- METODE PENGAMBILAN -->
    <div class="card mb-1 shadow metode-pengambilan">
        <div class="card-header fw-bold bg-white"><i class="bi bi-truck me-2"></i>Metode Pengambilan</div>
        <div class="card-body">
            <p class="text-muted mb-2">Metode pengambilan barang</p>
            <div class="d-flex justify-content-center gap-2">
                @if($transaksi->metode_pengambilan === 'ambil di toko')
                <span class="btn-metode-pengambilan btn w-75 text-white active" style="background-color: #058DA9;">Di Toko</span>
                @elseif($transaksi->metode_pengambilan === 'diantar')
                <span class="btn-metode-pengambilan btn w-75 text-white active" style="background-color: #0057B2;">Diantar</span>
                @endif
            </div>
        </div>
    </div>



    <!-- ALAMAT PENGIRIMAN -->
    <div class="card mb-1 shadow card-alamat">
        <div class="card-body">
            <label class="form-label fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>Alamat Pengiriman</label>
            <textarea class="form-control" rows="3" readonly>{{ $transaksi->metode_pengambilan === 'diantar' ? $transaksi->alamat_pengambilan : 'Tidak ada alamat pengiriman' }}</textarea>
        </div>
    </div>

    <!-- CATATAN -->
    <div class="card mb-4 shadow card-catatan">
        <div class="card-body">
            <label class="form-label fw-bold"><i class="bi bi-journal-text me-2"></i>Catatan</label>
            <textarea class="form-control" rows="3" readonly>{{ $transaksi->catatan ?: 'Tidak ada catatan' }}</textarea>
        </div>
    </div>
    @endif



    <!-- TOMBOL AKSI -->
    <div class="d-none d-md-flex gap-2 mb-2 justify-content-center mobile-btn-container fixed-bottom-aksi">
        <a href="#" onclick="history.back()" class="btn w-100 text-white mobile-btn-small" style="background:#6c757d;">Kembali</a>
    </div>
    </div>
</form>
@endsection