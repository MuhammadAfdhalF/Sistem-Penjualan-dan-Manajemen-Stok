@extends('layouts.mantis')

@section('title', 'Halaman Tambah Transaksi Offline')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h4 class="card-title mb-2 mb-md-0">Form Tambah Transaksi Offline</h4>
        <a href="{{ route('transaksi_offline.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>

    <div class="card-body">

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('transaksi_offline.store') }}" method="POST" id="formTransaksi">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Kode Transaksi</label>
                    <input type="text" name="kode_transaksi" class="form-control" value="{{ $kode_transaksi }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Transaksi</label>
                    <input type="datetime-local" name="tanggal" class="form-control" value="{{ $tanggal->format('Y-m-d\TH:i') }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jenis Pelanggan</label>
                    <select name="jenis_pelanggan" id="jenis_pelanggan" class="form-select" required>
                        <option value="">-- Pilih Jenis Pelanggan --</option>
                        <option value="Individu">Individu</option>
                        <option value="Toko Kecil">Toko Kecil</option>
                    </select>
                </div>
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label">Produk</label>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="produkTable">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Satuan</th>
                                <th>Harga (Rp)</th>
                                <th>Jumlah</th>
                                <th>Subtotal (Rp)</th>
                                <th class="text-center" style="width: 60px">
                                    <button type="button" class="btn btn-sm btn-success" id="addRow">
                                        <i class="ti ti-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="product-row">
                                <td>
                                    <select name="produk_id[]" class="form-select produk-select" required>
                                        <option value="">Pilih Produk</option>
                                        @forelse($produk as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_produk }}</option>
                                        @empty
                                        <option value="">Tidak ada produk tersedia</option>
                                        @endforelse
                                    </select>
                                </td>
                                <td>
                                    <select name="satuan_id[]" class="form-select satuan-select" required>
                                        <option value="">Pilih Satuan</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="harga[]" class="form-control harga text-end" readonly>
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control jumlah" min="0.01" step="0.01" value="1" required>
                                </td>
                                <td>
                                    <input type="text" name="subtotal[]" class="form-control subtotal text-end" readonly>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger removeRow">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <hr>

            <div class="row justify-content-end g-3">
                <div class="col-md-4">
                    <label class="form-label">Total</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="total" id="total" class="form-control text-end" readonly required>
                    </div>

                    <label class="form-label mt-3">Dibayar</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="dibayar" id="dibayar" class="form-control text-end" required>
                    </div>

                    <label class="form-label mt-3">Kembalian</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="kembalian" id="kembalian" class="form-control text-end" readonly>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@include('transaksi_offline.form_script')
@endsection