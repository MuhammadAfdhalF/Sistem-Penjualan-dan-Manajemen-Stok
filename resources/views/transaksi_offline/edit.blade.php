@extends('layouts.mantis')

@section('title', 'Halaman Edit Transaksi Offline')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h4 class="card-title mb-2 mb-md-0">Form Edit Transaksi Offline</h4>
        <a href="{{ route('transaksi_offline.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>

    <div class="card-body">
        <form action="{{ route('transaksi_offline.update', $transaksi->id) }}" method="POST" id="formTransaksi">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-label">Kode Transaksi</label>
                        <input type="text" class="form-control" value="{{ $transaksi->kode_transaksi }}" readonly>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-label">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tanggal" id="tanggal" class="form-control" value="{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('Y-m-d\TH:i') }}">
                    </div>
                </div>
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label">Produk</label>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="produkTable">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 180px;">Produk</th>
                                <th style="min-width: 130px;">Tipe Harga</th>
                                <th style="min-width: 110px;">Harga</th>
                                <th style="min-width: 90px;">Jumlah</th>
                                <th style="min-width: 130px;">Subtotal</th>
                                <th style="width: 90px;" class="text-center">
                                    <button type="button" class="btn btn-xs btn-success px-2 py-1" id="addRow" aria-label="Tambah Baris Produk" style="font-size: 0.75rem;">
                                        <i class="ti ti-plus"></i>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaksi->detail as $item)
                            <tr class="product-row">
                                <td>
                                    <select name="produk_id[]" class="form-select produk-select" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach($produk as $p)
                                        <option
                                            value="{{ $p->id }}"
                                            data-harga-normal="{{ $p->harga_normal }}"
                                            data-harga-grosir="{{ $p->harga_grosir ?? 0 }}"
                                            {{ $p->id == $item->produk_id ? 'selected' : '' }}>
                                            {{ $p->nama_produk }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="tipe_harga[]" class="form-select tipe-harga-select" required>
                                        <option value="normal" {{ ($item->tipe_harga ?? 'normal') == 'normal' ? 'selected' : '' }}>Harga Normal</option>
                                        <option value="grosir" {{ ($item->tipe_harga ?? '') == 'grosir' ? 'selected' : '' }}>Harga Grosir</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="harga[]" class="form-control harga"  value="{{ number_format($item->harga, 0, ',', '.') }}">
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control jumlah" min="1" value="{{ $item->jumlah }}">
                                </td>
                                <td>
                                    <input type="text" name="subtotal[]" class="form-control subtotal" readonly value="{{ number_format($item->subtotal, 0, ',', '.') }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger removeRow" aria-label="Hapus Baris Produk">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <hr>

            <div class="row justify-content-end g-3">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="form-group mb-3">
                        <label class="form-label">Total</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="total" id="total" class="form-control" readonly value="{{ number_format($transaksi->total, 0, ',', '.') }}">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Dibayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="dibayar" id="dibayar" class="form-control" value="{{ number_format($transaksi->dibayar, 0, ',', '.') }}">
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Kembalian</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="kembalian" id="kembalian" class="form-control" readonly value="{{ number_format($transaksi->kembalian, 0, ',', '.') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group text-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Update Transaksi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@include('transaksi_offline.form_script')
@endsection