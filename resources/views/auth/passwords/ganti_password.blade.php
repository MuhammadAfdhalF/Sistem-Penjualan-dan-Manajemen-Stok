@extends('layouts.mantis')

@section('title', 'Ganti Password')

@section('breadcrumb')
<li class="breadcrumb-item">Dashboard</li>
<li class="breadcrumb-item"><strong><a href="#">Ganti Password</a></strong></li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Ganti Password</h4>
        <a href="{{ route('dashboard.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
    <div class="card-body">
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
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <div class="mb-3 position-relative"> {{-- Tambahkan position-relative di sini --}}
                <label for="current_password" class="form-label">Password Lama</label>
                <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                <button type="button" onclick="togglePassword('current_password', this)" class="position-absolute" style="top: 38px; right: 20px; background: transparent; border: none; cursor: pointer; color: #6b7280;">
                    <i class="fas fa-eye"></i>
                </button>
                @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 position-relative"> {{-- Tambahkan position-relative di sini --}}
                <label for="password" class="form-label">Password Baru</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                <button type="button" onclick="togglePassword('password', this)" class="position-absolute" style="top: 38px; right: 20px; background: transparent; border: none; cursor: pointer; color: #6b7280;">
                    <i class="fas fa-eye"></i>
                </button>
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 position-relative"> {{-- Tambahkan position-relative di sini --}}
                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="new-password">
                <button type="button" onclick="togglePassword('password_confirmation', this)" class="position-absolute" style="top: 38px; right: 20px; background: transparent; border: none; cursor: pointer; color: #6b7280;">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn btn-primary">Ganti Password</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
