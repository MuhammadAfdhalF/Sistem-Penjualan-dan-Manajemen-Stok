@extends('layouts.template_mobile')
@section('title', 'Riwayat Belanja - KZ Family')

@push('head')
<style>
    .card-riwayat {
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 1rem;
    }

    .badge-status {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 50px;
    }

    .text-small {
        font-size: 0.875rem;
    }

    .riwayat-header-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        main.main-content {
            margin-bottom: 0px;
            padding-bottom: 0px;
        }
    }

    @media (max-width: 576px) {
        main.main-content {
            margin-bottom: 0px;
            padding-bottom: 0px;
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
<div class="container-fluid py-2 px-2" style="max-width: 1280px;">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 ms-3 mt-3 d-block d-lg-none">
        <div>
            <h6 class="fw-bold mb-1 text-body">Toko KZ Family</h6>
            <small class="text-muted">Riwayat Belanja Anda</small>
        </div>
        <div class="me-3">
            <a href="{{ route('mobile.keranjang.index') }}" class="btn bg-white shadow rounded-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-cart text-dark" style="font-size: 1.2rem;"></i>
            </a>
        </div>
    </div>

    <!-- Judul -->
    <div class="text-center mb-1">
        <div class="p-2 shadow" style="border-radius: 8px; background-color: #ffffff; border: 1px solid rgba(0, 0, 0, 0.18);">
            <strong class="fs-6">Riwayat Pesananan Anda</strong>
        </div>
    </div>

    <!-- Card Riwayat -->
    @forelse ($riwayat as $trx)
    <div class="card card-riwayat mb-1 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">{{ \Carbon\Carbon::parse($trx->tanggal)->translatedFormat('d F Y') }}</small>
                @php
                $isOnline = $trx->tipe === 'online';
                $statusClass = $isOnline
                ? ($trx->status_transaksi === 'selesai' ? 'bg-success text-white' : 'bg-warning text-dark')
                : 'bg-secondary text-white';
                $statusText = $isOnline ? 'Online - ' . ucfirst($trx->status_transaksi) : 'Offline';
                @endphp
                <span class="badge badge-status {{ $statusClass }}">
                    {{ $statusText }}
                </span>
            </div>

            <div class="riwayat-header-title fs-6">Pesanan Anda :</div>
            <ul class="mb-2 ps-3 ms-3">
                @php
                $produkList = [];

                foreach ($trx->detail as $detail) {
                foreach ($detail->jumlah_json ?? [] as $satuanId => $qty) {
                $qty = floatval($qty);
                if ($qty <= 0) continue;

                    $produkId=$detail->produk_id;
                    $nama = $detail->produk->nama_produk;
                    $satuan = $detail->produk->satuans->firstWhere('id', $satuanId);
                    $unit = $satuan->nama_satuan ?? 'unit';

                    if (!isset($produkList[$produkId])) {
                    $produkList[$produkId] = [
                    'nama' => $nama,
                    'satuan' => [],
                    ];
                    }

                    $produkList[$produkId]['satuan'][] = "{$qty} × {$unit}";
                    }
                    }

                    $produkArray = array_values($produkList);
                    @endphp

                    @foreach ($produkArray as $index => $item)
                    @if ($index < 2)
                        <li class="text-small">
                        <div class="d-flex justify-content-between">
                            <span class="me-2">{{ $item['nama'] }}</span>
                            <span class="text-nowrap">{{ implode(', ', $item['satuan']) }}</span>
                        </div>
                        </li>
                        @endif
                        @endforeach

                        @if (count($produkArray) > 2)
                        <li class="text-small">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">..... (klik untuk selengkapnya)</span>
                                <span>&nbsp;</span>
                            </div>
                        </li>
                        @endif
            </ul>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="d-flex align-items-center gap-2 text-small fw-semibold">
                    <i class="bi bi-wallet2"></i>
                    <span>Total Harga</span>
                </div>
                <div class="fw-bold text-small">Rp. {{ number_format($trx->total, 0, ',', '.') }}</div>
            </div>

            <div class="text-end mt-2">
                <a href="{{ route('mobile.detail_riwayat_belanja.index', ['tipe' => $trx->tipe, 'id' => $trx->id]) }}">
                    Selengkapnya...
                </a>

            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted mt-4">Belum ada riwayat transaksi.</div>
    @endforelse
</div>
@endsection