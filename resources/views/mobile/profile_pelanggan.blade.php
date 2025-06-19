@extends('layouts.template_mobile')
@section('title', 'Halaman Profile Pelanggan - KZ Family')

@section('content')
<style>
    main.main-content {
        min-height: auto;
        padding-bottom: 20px;
        overflow-x: hidden;
    }

    .popup-success {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        padding: 18px 24px;
        border-radius: 16px;
        box-shadow: 0 6px 36px rgba(0, 0, 0, 0.18);
        animation: fadeZoomIn 0.4s ease, fadeZoomOut 0.4s ease 2.8s forwards;
        z-index: 9999;
        text-align: center;
        font-weight: 600;
        font-size: 1rem;
        color: #135e3d;
        min-width: 280px;
        max-width: 90vw;
    }

    .popup-success .icon-circle {
        background-color: #2ecc71;
        width: 54px;
        height: 54px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.8rem;
        margin-bottom: 10px;
    }

    .popup-success .popup-text {
        font-size: 1rem;
        font-weight: 600;
        color: #135e3d;
    }

    @keyframes fadeZoomIn {
        from {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }

    @keyframes fadeZoomOut {
        to {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.9);
        }
    }

    .form-control[readonly],
    .form-control:disabled,
    textarea.form-control:disabled {
        pointer-events: none;
        background-color: #f8f9fa !important;
        color: #212529;
        border: 1.5px solid #ced4da !important;
        box-shadow: 0 0 0 1px #ced4da;
    }

    .form-control,
    textarea.form-control {
        border: 1.5px solid #ced4da;
        box-shadow: 0 0 0 1px #ced4da;
        font-size: 1rem;
    }

    .form-label {
        font-size: 1rem;
    }

    .btn {
        font-size: 1rem;
    }

    h5.page-title {
        font-size: 1.5rem;
    }

    @media (max-width: 576px) {

        .form-control,
        textarea.form-control,
        .form-label,
        .btn {
            font-size: 0.85rem;
        }

        h5.page-title {
            font-size: 1.2rem;
        }
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }

    @media (max-width: 1280px) and (orientation: landscape),
    (min-width: 600px) and (max-width: 1024px) {
        main.main-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
    }
</style>

<div class="container py-4" style="max-width: 1000px;">
    <!-- HANYA SATU JUDUL -->
    <h5 class="text-center fw-bold mb-4 mt-2 mt-sm-0 page-title">Profile Anda</h5>

    <div class="text-center mb-3">
        <div data-bs-toggle="modal" data-bs-target="#fotoProfilModal" style="cursor:pointer;">
            <div class="mx-auto rounded-circle bg-secondary" style="width:120px; height:120px; display:flex; align-items:center; justify-content:center; overflow: hidden;">
                @if ($user->foto_user)
                <img src="{{ asset('storage/' . $user->foto_user) }}" class="rounded-circle" style="width:100%; height:100%; object-fit:cover;">
                @else
                <span style="font-size: 2rem; color: #999;">X</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Lihat Foto -->
    <div class="modal fade" id="fotoProfilModal" tabindex="-1" aria-labelledby="fotoProfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center p-0">
                    @if ($user->foto_user)
                    <img src="{{ asset('storage/' . $user->foto_user) }}" class="img-fluid rounded" alt="Foto Profil">
                    @else
                    <div class="bg-light text-muted p-5 rounded">Tidak ada foto</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <form>
        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Nama</label>
            <input type="text" class="form-control" value="{{ $user->nama }}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Email</label>
            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Nomor Hp</label>
            <input type="text" class="form-control" value="{{ $user->no_hp }}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Umur</label>
            <input type="number" class="form-control" value="{{ $user->umur }}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Jenis Pelanggan</label>
            <input type="text" class="form-control" value="{{ $user->jenis_pelanggan }}" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Alamat</label>
            <textarea class="form-control" rows="3" disabled>{{ $user->alamat }}</textarea>
        </div>


    </form>
    <div class="d-flex justify-content-between mt-4">
        <form method="POST" action="{{ route('logout') }}" class="w-50 me-2">
            @csrf
            <button type="submit" class="btn w-100" style="background-color: #BB2124; color: #fff;">
                Logout
            </button>
        </form>

        <a href="{{ route('mobile.profile_pelanggan.edit') }}" class="btn w-50 ms-2" style="background-color: #0572BA; color: #fff;">
            Update Profile
        </a>
    </div>

</div>

@if(session('success'))
<div id="popup-success" class="popup-success d-flex flex-column align-items-center justify-content-center">
    <div class="icon-circle">
        <i class="bi bi-check2"></i>
    </div>
    <div class="popup-text">
        {{ session('success') }}
    </div>
</div>
@endif

@push('scripts')
<script>
    setTimeout(() => {
        const popup = document.getElementById('popup-success');
        if (popup) popup.remove();
    }, 3500);
</script>
@endpush
@endsection