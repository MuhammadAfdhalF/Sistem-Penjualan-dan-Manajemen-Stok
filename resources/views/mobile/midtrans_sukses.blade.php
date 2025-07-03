@extends('layouts.template_mobile')

@section('title', 'Pembayaran Berhasil')

@push('head')
<style>
    /* Custom CSS untuk tampilan yang lebih mirip gambar */
    body {
        background-color: #f8f9fa; /* Warna latar belakang umum */
        font-family: 'Inter', sans-serif; /* Menggunakan font Inter */
    }

    .payment-success-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 60px); /* Sesuaikan tinggi agar card di tengah vertikal, kurangi tinggi footer/header jika ada */
        padding: 1rem; /* Padding untuk responsivitas di mobile */
    }

    .payment-success-card {
        background-color: #fff;
        border-radius: 16px; /* Rounded corners pada card */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Shadow pada card */
        padding: 2rem;
        text-align: center;
        width: 100%;
        max-width: 400px; /* Lebar maksimal card */
    }

    .check-circle {
        width: 100px; /* Ukuran lingkaran */
        height: 100px;
        background-color: #0056b3; /* Warna biru lingkaran */
        border-radius: 50%; /* Membuat lingkaran */
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0 auto 1.5rem auto; /* Tengah dan margin bawah */
    }

    .check-circle i {
        color: #fff; /* Warna ikon centang */
        font-size: 3.5rem; /* Ukuran ikon centang */
    }

    .payment-amount {
        font-size: 1.8rem; /* Ukuran font jumlah pembayaran */
        font-weight: bold;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .thank-you-text {
        font-size: 1rem;
        color: #666;
        margin-bottom: 1.5rem;
    }

    .btn-lihat-pesanan {
        background-color: #17a2b8; /* Warna biru tosca untuk tombol */
        border-color: #17a2b8;
        color: #fff;
        font-size: 1.1rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px; /* Rounded corners pada tombol */
        transition: all 0.2s ease-in-out;
    }

    .btn-lihat-pesanan:hover {
        background-color: #138496; /* Warna hover */
        border-color: #138496;
        transform: translateY(-2px); /* Efek hover */
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .payment-success-card {
            padding: 1.5rem;
        }
        .check-circle {
            width: 80px;
            height: 80px;
        }
        .check-circle i {
            font-size: 2.8rem;
        }
        .payment-amount {
            font-size: 1.5rem;
        }
        .thank-you-text {
            font-size: 0.9rem;
        }
        .btn-lihat-pesanan {
            font-size: 0.95rem;
            padding: 0.6rem 1.2rem;
        }
    }
</style>
@endpush

@section('content')
<div class="payment-success-container">
    <div class="payment-success-card">
        <h4 class="fw-bold mb-4">Pembayaran Berhasil</h4>
        <div class="check-circle">
            <i class="bi bi-check-lg"></i>
        </div>

        <p class="thank-you-text">Terimkasih sudah berbelanja !!!</p>
        <a href="{{ route('mobile.riwayat_belanja.index') }}" class="btn btn-lihat-pesanan">Lihat Pesanan Anda</a>
    </div>
</div>
@endsection
