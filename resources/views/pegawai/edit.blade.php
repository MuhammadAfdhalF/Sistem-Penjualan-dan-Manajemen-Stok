@extends('layouts.mantis')

@section('title')
Halaman Edit Pegawai
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Form Edit Data Pegawai</h4>
            <div>
                <a href="{{ route('pegawai.index') }}">Kembali</a>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="nama_pegawai">Nama Pegawai</label>
                    <input type="text" name="nama_pegawai" id="nama_pegawai" class="form-control @error('nama_pegawai') is-invalid @enderror" placeholder="Masukkan nama pegawai" value="{{ old('nama_pegawai', $pegawai->nama_pegawai) }}" autofocus>
                    @error('nama_pegawai')
                    <small class="text-danger bg-white d-inline-block px-2 py-1 rounded mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="umur">Umur</label>
                    <input type="number" name="umur" id="umur" class="form-control @error('umur') is-invalid @enderror" placeholder="Masukkan umur" value="{{ old('umur', $pegawai->umur) }}">
                    @error('umur')
                    <small class="text-danger bg-white d-inline-block px-2 py-1 rounded mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="foto">Foto Pegawai</label><br>
                    @if ($pegawai->foto)
                    <img src="{{ asset('storage/foto_pegawai/' . $pegawai->foto) }}" width="120" class="mb-2">
                    @endif
                    <input type="file" name="foto" id="foto" class="form-control @error('foto') is-invalid @enderror">
                    @error('foto')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>



                <div class="form-group mb-3">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', $pegawai->tanggal_lahir) }}">
                    @error('tanggal_lahir')
                    <small class="text-danger bg-white d-inline-block px-2 py-1 rounded mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="tempat_lahir">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" placeholder="Masukkan tempat lahir" value="{{ old('tempat_lahir', $pegawai->tempat_lahir) }}">
                    @error('tempat_lahir')
                    <small class="text-danger bg-white d-inline-block px-2 py-1 rounded mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror">
                        <option value="" disabled {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == '' ? 'selected' : '' }}>Pilih Jenis Kelamin</option>
                        <option value="laki-laki" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                    <small class="text-danger bg-white d-inline-block px-2 py-1 rounded mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="alamat">Alamat</label>
                    <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="4" placeholder="Masukkan alamat lengkap">{{ old('alamat', $pegawai->alamat) }}</textarea>
                    @error('alamat')
                    <small class="text-danger bg-white d-inline-block px-2 py-1 rounded mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group text-end">
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection