@extends('layouts.mantis')

@section('title', 'Tambah Catatan Keuangan')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen</li>
<li class="breadcrumb-item"><a href="{{ route('keuangan.index') }}">Keuangan</a></li>
<li class="breadcrumb-item active">Tambah Catatan</li>
@endsection

<head>
    <title>Halaman Tambah Keuangan</title>
</head>

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Form Tambah Keuangan</h4>
        <a href="{{ route('keuangan.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <form action="{{ route('keuangan.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control"
                        value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>

                <div class="col-md-6">
                    <label for="jenis" class="form-label">Jenis Transaksi</label>
                    <select name="jenis" id="jenis" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="pemasukan" {{ old('jenis') == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="pengeluaran" {{ old('jenis') == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="nominal_formatted" class="form-label">Nominal</label>
                    <input type="text" name="nominal_formatted" id="nominal_formatted" class="form-control"
                        value="{{ number_format(old('nominal', $keuangan->nominal ?? 0), 0, ',', '.') }}" required>
                    <input type="hidden" name="nominal" id="nominal" value="{{ old('nominal', $keuangan->nominal ?? 0) }}">
                </div>


                <div class="col-12">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3"
                        placeholder="Contoh: Pembelian bahan baku / pemasukan dari event">{{ old('keterangan') }}</textarea>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formattedInput = document.getElementById('nominal_formatted');
        const rawInput = document.getElementById('nominal');

        formattedInput.addEventListener('input', function() {
            // Hapus semua non-digit
            let cleanValue = formattedInput.value.replace(/\D/g, '');

            // Update hidden input
            rawInput.value = cleanValue;

            // Format angka ribuan
            formattedInput.value = new Intl.NumberFormat('id-ID').format(cleanValue);
        });
    });
</script>
@endpush