@extends('layouts.mantis')

@section('title', 'Halaman Tambah Transaksi Online')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h4 class="card-title mb-2 mb-md-0">Form Tambah Transaksi Online</h4>
        <a href="{{ route('transaksi_online.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>

    <div class="card-body">
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('transaksi_online.store') }}" method="POST" id="formTransaksiOnline">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Pelanggan</label>
                    <select name="user_id" class="form-select" required id="selectPelanggan">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach ($users as $user)
                        <option value="{{ $user->id }}" data-jenis="{{ $user->jenis_pelanggan }}">{{ $user->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal</label>
                    <input type="datetime-local" name="tanggal" class="form-control" value="{{ now()->format('Y-m-d\\TH:i') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="form-select" required>
                        <option value="payment_gateway">Payment Gateway</option>
                        <option value="cod">COD</option>
                        <option value="bayar_di_toko">Bayar di Toko</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-select" required>
                        <option value="pending">Pending</option>
                        <option value="lunas">Lunas</option>
                        <option value="gagal">Gagal</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Transaksi</label>
                    <select name="status_transaksi" class="form-select" required>
                        <option value="diproses">Diproses</option>
                        <option value="diantar">Diantar</option>
                        <option value="diambil">Diambil</option>
                        <option value="selesai">Selesai</option>
                        <option value="batal">Batal</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ambil di Toko?</label>
                    <select name="diambil_di_toko" class="form-select" required>
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Alamat Pengambilan / Pengiriman</label>
                    <textarea name="alamat_pengambilan" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label">Daftar Produk</label>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="produkTable">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah Bertingkat</th>
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
                                        @foreach ($produkOptions as $item)
                                        <option
                                            value="{{ $item['id'] }}"
                                            data-satuan='@json($item['satuans'])'
                                            data-harga='@json($item['harga'])'>
                                            {{ $item['nama_produk'] }}
                                        </option>
                                        @endforeach
                                    </select>

                                </td>
                                <td>
                                    <div class="satuan-jumlah-list"></div>
                                    <input type="hidden" name="jumlah_json[]" class="jumlah-json">
                                </td>
                                <td>
                                    <input type="text" class="form-control subtotal" readonly>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger removeRow">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-end mt-2 fw-bold">
                        <span id="totalDisplay">Rp 0</span>
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
@include('transaksi_online.form_script')
@endsection