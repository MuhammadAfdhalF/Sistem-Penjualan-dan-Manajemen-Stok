@extends('layouts.mantis')

@section('title')
Halaman Tambah Pelanggan
@endsection

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen Stok</li>
<li class="breadcrumb-item"><a href="{{ route('pelanggan.index') }}" style="opacity: 0.5;">Pelanggan</a></li>
<li class="breadcrumb-item"><strong><a href="{{ route('pelanggan.create') }}">Tambah Data Pelanggan</a></strong></li>
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Form Tambah Data Pelanggan</h4>
            <a href="{{ route('pelanggan.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>

        <div class="card-body">
            <form action="{{ route('pelanggan.store') }}" method="POST">
                @csrf

                {{-- Hidden role --}}
                <input type="hidden" name="role" value="pelanggan">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Masukkan nama pelanggan" value="{{ old('nama') }}" autofocus>
                        @error('nama')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="Masukkan email" value="{{ old('email') }}">
                        @error('email')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="no_hp" class="form-label">No HP</label>
                        <input type="text" name="no_hp" id="no_hp" class="form-control @error('no_hp') is-invalid @enderror" placeholder="Masukkan nomor HP" value="{{ old('no_hp') }}">
                        @error('no_hp')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="umur" class="form-label">Umur</label>
                        <input type="number" name="umur" id="umur" class="form-control @error('umur') is-invalid @enderror" placeholder="Masukkan umur" value="{{ old('umur') }}">
                        @error('umur')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="jenis_pelanggan" class="form-label">Jenis Pelanggan</label>
                        <select name="jenis_pelanggan" id="jenis_pelanggan" class="form-control @error('jenis_pelanggan') is-invalid @enderror">
                            <option value="" disabled {{ old('jenis_pelanggan') ? '' : 'selected' }}>Pilih Jenis</option>
                            <option value="Toko Kecil" {{ old('jenis_pelanggan') == 'Toko Kecil' ? 'selected' : '' }}>Toko Kecil</option>
                            <option value="Individu" {{ old('jenis_pelanggan') == 'Individu' ? 'selected' : '' }}>Individu</option>
                        </select>
                        @error('jenis_pelanggan')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" placeholder="Masukkan alamat pelanggan">{{ old('alamat') }}</textarea>
                        @error('alamat')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Password with toggle icon -->
                    <div class="col-md-6 mb-3 position-relative">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Masukkan password">
                        <button type="button" onclick="togglePassword('password', this)" class="position-absolute" style="top: 38px; right: 20px; background: transparent; border: none; cursor: pointer; color: #6b7280;">
                            <i class="fas fa-eye"></i>
                        </button>

                        @error('password')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Password confirmation with toggle icon -->
                    <div class="col-md-6 mb-3 position-relative">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="form-control"
                            placeholder="Ulangi password">
                        <button type="button" onclick="togglePassword('password_confirmation', this)" class="position-absolute" style="top: 38px; right: 20px; background: transparent; border: none; cursor: pointer; color: #6b7280;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group text-end">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
@endsection