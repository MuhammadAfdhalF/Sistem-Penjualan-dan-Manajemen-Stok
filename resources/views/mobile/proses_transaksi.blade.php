@extends('layouts.template_mobile')
@section('title', 'Halaman Proses Transaksi - KZ Family')

@push('head')
<style>
    .btn-pengambilan.active,
    .btn-pembayaran.active {
        border: 2px solid #fff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5);
        transform: scale(0.97);
        opacity: 1;
    }

    .btn-pengambilan,
    .btn-pembayaran {
        transition: all 0.2s ease-in-out;
    }

    .btn-aksi {
        transition: all 0.2s ease-in-out;
    }

    .btn-aksi:active {
        transform: scale(0.96);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 576px) {
        .footer-mobile-nav {
            display: none !important;
        }

        .rincian-header .col-4,
        .rincian-header .col-2,
        .rincian-header .col-3,
        .rincian-body .col-4,
        .rincian-body .col-2,
        .rincian-body .col-3 {
            font-size: 0.8rem;
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
            height: 35px;
            padding: 0.5rem 1rem;
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
        <div class="bg-white shadow-sm mb-1 d-block d-lg-none">
            <div class="px-3 py-2"><i class="bi bi-arrow-left fs-3" style="cursor:pointer;"></i></div>
            <div class="px-3 pb-3 pt-2 bg-white">
                <div class="fw-bold fs-6 fs-md-5">Toko KZ Family</div>
                <div class="text-muted mobile-fs-xs fs-md-6">Proses Transaksi</div>
            </div>
        </div>

        {{-- RINCIAN BELANJA --}}
        <div class="card mb-1 shadow">
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
        <div class="card mb-1 shadow">
            <div class="card-header fw-bold bg-white"><i class="bi bi-truck me-2"></i>Metode Pengambilan</div>
            <div class="card-body">
                <p class="text-muted mb-2">Pilih metode pengambilan barang</p>
                <div class="d-flex gap-2 mobile-justify-center">
                    <button type="button" class="btn w-50 text-white btn-pengambilan" data-value="ambil di toko" style="background-color: #058DA9;">Di Toko</button>
                    <button type="button" class="btn w-50 text-white btn-pengambilan" data-value="diantar" style="background-color: #0057B2;">Diantar</button>
                </div>
            </div>
        </div>

        {{-- METODE PEMBAYARAN --}}
        <div class="card mb-1 shadow">
            <div class="card-header fw-bold bg-white"><i class="bi bi-wallet2 me-2"></i>Metode Pembayaran</div>
            <div class="card-body">
                <p class="text-muted mb-2">Pilih metode pembayaran</p>
                <div class="d-flex gap-2 mobile-justify-center">
                    <button type="button" class="btn w-50 text-white btn-pembayaran" data-value="bayar_di_toko" style="background-color: #058DA9;">Cash</button>
                    <button type="button" class="btn w-50 text-white btn-pembayaran" data-value="payment_gateway" style="background-color: #0057B2;">Digital</button>
                </div>
            </div>
        </div>

        {{-- ALAMAT PENGIRIMAN --}}
        <div class="card mb-1 shadow" id="alamat-card">
            <div class="card-body px-3 pt-3 pb-2">
                <label class="form-label fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>Alamat Pengiriman</label>
                <textarea class="form-control py-2" rows="3" name="alamat_pengambilan" placeholder="Masukkan alamat pengiriman"></textarea>
            </div>
        </div>

        {{-- CATATAN --}}
        <div class="card mb-4 shadow">
            <div class="card-body px-3 pt-3 pb-2">
                <label class="form-label fw-bold"><i class="bi bi-journal-text me-2"></i>Catatan</label>
                <textarea class="form-control py-2" rows="3" name="catatan" placeholder="Tambahkan catatan..."></textarea>
            </div>
        </div>

        {{-- TOMBOL --}}
        <div class="d-flex gap-2 mb-5 justify-content-center">
            <a href="{{ route('mobile.keranjang.index') }}" class="btn w-50" style="background:#c62828; color:#fff;">Batalkan</a>
            <button type="submit" class="btn w-50" style="background:#0d47a1; color:#fff;">Buat Pesanan</button>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
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