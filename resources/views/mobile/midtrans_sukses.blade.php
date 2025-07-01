@extends('layouts.template_mobile')

@section('title', 'Pembayaran Berhasil')

@section('content')
<div class="container py-5 text-center">
    <div class="card shadow-sm p-4">
        <div class="mb-4">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        </div>
        <h4 class="fw-bold mb-3">Pembayaran Berhasil!</h4>
        <p class="mb-4">Terima kasih telah melakukan pembayaran. Pesanan Anda sedang kami proses.</p>
        <a href="{{ route('mobile.home.index') }}" class="btn btn-success px-4 py-2">Kembali ke Beranda</a>
    </div>
</div>
@endsection
