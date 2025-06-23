@extends('layouts.template_mobile')
@section('title', 'Edit Profil Pelanggan - KZ Family')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
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

<div class="container py-4" style="max-width: 1280px;">

    <form method="POST" action="{{ route('mobile.profile_pelanggan.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h5 class="text-center fw-bold mb-4 page-title">Edit Profil Anda</h5>

        {{-- Tambahkan alert untuk pesan success/error --}}
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Oops!</strong> Ada masalah dengan input Anda.
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif


        {{-- FOTO PROFIL --}}
        <div class="mb-4 text-center">
            <label for="foto_user" style="cursor: pointer; position: relative; display: inline-block;">
                <div class="mx-auto rounded-circle bg-secondary" style="width:120px; height:120px; display:flex; align-items:center; justify-content:center; overflow: hidden;">
                    <img id="previewFoto" src="{{ $user->foto_user ? asset('storage/' . $user->foto_user) : 'https://via.placeholder.com/120?text=+' }}"
                        class="rounded-circle"
                        style="width:100%; height:100%; object-fit:cover;"
                        alt="Foto Profil">
                    <div style="position: absolute; bottom: 0; right: 0; background: #fff; border-radius: 50%; padding: 6px;">
                        <i class="bi bi-camera" style="font-size: 1rem;"></i>
                    </div>
                </div>
            </label>
            <input type="file" id="foto_user" name="foto_user" class="d-none" accept="image/*" onchange="previewGambar(event)">
            <p class="text-muted mt-2" style="font-size: 0.85rem;">Klik foto untuk mengubah</p>
        </div>

        {{-- FORM --}}

        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Nama</label>
            <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama', $user->nama) }}" required>
            @error('nama')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Nomor Hp</label>
            <input type="text" class="form-control @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required>
            @error('no_hp')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Ganti input Umur dengan Tanggal Lahir --}}
        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Tanggal Lahir</label>
            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir?->format('Y-m-d')) }}" required>
            @error('tanggal_lahir')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Jenis Pelanggan</label>
            <select class="form-control @error('jenis_pelanggan') is-invalid @enderror" name="jenis_pelanggan" required>
                <option value="Toko Kecil" {{ old('jenis_pelanggan', $user->jenis_pelanggan) == 'Toko Kecil' ? 'selected' : '' }}>Toko Kecil</option>
                <option value="Individu" {{ old('jenis_pelanggan', $user->jenis_pelanggan) == 'Individu' ? 'selected' : '' }}>Individu</option>
            </select>
            @error('jenis_pelanggan')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Alamat</label>
            <textarea class="form-control @error('alamat') is-invalid @enderror" name="alamat" rows="3" required>{{ old('alamat', $user->alamat) }}</textarea>
            @error('alamat')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Password Baru</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Biarkan kosong jika tidak diubah">
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold ms-2">Konfirmasi Password</label>
            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" placeholder="Ulangi password baru">
            @error('password_confirmation') {{-- Pastikan ini juga dicek jika ada error --}}
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('mobile.profile_pelanggan.index') }}" class="btn w-50 me-2 text-white fw-semibold" style="background-color: #BB2124;">
                Batalkan
            </a>
            <button type="submit" class="btn w-50 ms-2 text-white fw-semibold" style="background-color: #0572BA;">
                Update Profile
            </button>
        </div>

    </form>

</div>

{{-- PREVIEW JS --}}
@push('scripts')
<script>
    function previewGambar(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('previewFoto');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endpush

@endsection