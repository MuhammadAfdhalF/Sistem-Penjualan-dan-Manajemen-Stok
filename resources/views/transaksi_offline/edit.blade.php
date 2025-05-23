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

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Kode Transaksi</label>
                    <input type="text" class="form-control" value="{{ $transaksi->kode_transaksi }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Transaksi</label>
                    <input type="datetime-local" name="tanggal" class="form-control" value="{{ \Carbon\Carbon::parse($transaksi->tanggal)->format('Y-m-d\\TH:i') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Pelanggan</label>
                    <select name="jenis_pelanggan" id="jenis_pelanggan" class="form-select" required>
                        <option value="">-- Pilih Jenis Pelanggan --</option>
                        <option value="Individu" {{ $transaksi->jenis_pelanggan == 'Individu' ? 'selected' : '' }}>Individu</option>
                        <option value="Toko Kecil" {{ $transaksi->jenis_pelanggan == 'Toko Kecil' ? 'selected' : '' }}>Toko Kecil</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nama Pelanggan</label>
                    <select name="pelanggan_id" id="pelanggan_id" class="form-select">
                        <option value="">-- Opsional --</option>
                        @foreach($pelanggans as $p)
                        <option value="{{ $p->id }}" {{ $transaksi->pelanggan_id == $p->id ? 'selected' : '' }} data-jenis="{{ $p->jenis_pelanggan }}">
                            {{ $p->nama }}
                        </option>
                        @endforeach
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
                            @foreach($transaksi->detail as $item)
                            <tr class="product-row">
                                <td>
                                    <select name="produk_id[]" class="form-select produk-select" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach($produk as $p)
                                        <option value="{{ $p->id }}" {{ $p->id == $item->produk_id ? 'selected' : '' }}>
                                            {{ $p->nama_produk }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="satuan_id[]" class="form-select satuan-select" required>
                                        <option value="{{ $item->satuan_id }}">{{ $item->satuan->nama_satuan ?? '-' }}</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="harga[]" class="form-control harga text-end" value="{{ number_format($item->harga, 0, ',', '.') }}" readonly>
                                    <input type="hidden" name="harga_id[]" class="harga-id" value="{{ $item->harga_id }}">
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control jumlah" min="1" value="{{ $item->jumlah }}">
                                </td>
                                <td>
                                    <input type="text" name="subtotal[]" class="form-control subtotal text-end" readonly value="{{ number_format($item->subtotal, 0, ',', '.') }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger removeRow">
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
                <div class="col-md-4">
                    <label class="form-label">Total</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="total" id="total" class="form-control text-end" readonly value="{{ number_format($transaksi->total, 0, ',', '.') }}">
                    </div>

                    <label class="form-label mt-3">Dibayar</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="dibayar" id="dibayar" class="form-control text-end" value="{{ number_format($transaksi->dibayar, 0, ',', '.') }}">
                    </div>

                    <label class="form-label mt-3">Kembalian</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="kembalian" id="kembalian" class="form-control text-end" readonly value="{{ number_format($transaksi->kembalian, 0, ',', '.') }}">
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
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