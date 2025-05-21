@extends('layouts.mantis')

@section('title', 'Edit Catatan Keuangan')

@section('breadcrumb')
<li class="breadcrumb-item">Sistem Manajemen</li>
<li class="breadcrumb-item"><a href="{{ route('keuangan.index') }}">Keuangan</a></li>
<li class="breadcrumb-item active">Edit Catatan</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Form Edit Keuangan</h4>
        <a href="{{ route('keuangan.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <form action="{{ route('keuangan.update', $keuangan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control"
                        value="{{ old('tanggal', $keuangan->tanggal ? $keuangan->tanggal->format('Y-m-d') : date('Y-m-d')) }}" required>
                </div>

                <div class="col-md-6">
                    <label for="jenis" class="form-label">Jenis Transaksi</label>
                    <select name="jenis" id="jenis" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="pemasukan" {{ old('jenis', $keuangan->jenis) == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="pengeluaran" {{ old('jenis', $keuangan->jenis) == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="nominal_formatted" class="form-label">Nominal</label>
                    <input type="text" id="nominal_formatted" class="form-control"
                        value="{{ number_format(old('nominal', $keuangan->nominal ?? 0), 0, ',', '.') }}" required>
                    <input type="hidden" name="nominal" id="nominal" value="{{ old('nominal', $keuangan->nominal ?? 0) }}">
                </div>

                <!-- <div class="col-md-6">
                    <label for="transaksi_id" class="form-label">Transaksi Offline (Opsional)</label>
                    <select name="transaksi_id" id="transaksi_id" class="form-select">
                        <option value="">-- Pilih Transaksi --</option>
                        @foreach ($transaksis as $transaksi)
                        <option value="{{ $transaksi->id }}"
                            {{ old('transaksi_id', $keuangan->transaksi_id) == $transaksi->id ? 'selected' : '' }}>
                            #{{ $transaksi->kode_transaksi }} - {{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d M Y') }}
                        </option>
                        @endforeach
                    </select>
                </div> -->

                <div class="col-12">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3"
                        placeholder="Contoh: Pembelian bahan baku / pemasukan dari event">{{ old('keterangan', $keuangan->keterangan) }}</textarea>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Update</button>
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
            // Bersihkan karakter selain angka
            let cleanValue = formattedInput.value.replace(/\D/g, '');

            // Update hidden input
            rawInput.value = cleanValue;

            // Format tampilan ke user
            formattedInput.value = new Intl.NumberFormat('id-ID').format(cleanValue);
        });
    });
</script>
@endpush