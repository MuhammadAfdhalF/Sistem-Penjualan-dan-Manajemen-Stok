@extends('layouts.mantis')

@section('title', 'Halaman Edit Transaksi Online')

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <h4 class="card-title mb-2 mb-md-0">Form Edit Transaksi Online</h4>
        <a href="{{ route('transaksi_online.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>

    <div class="card-body">
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('transaksi_online.update', $transaksiOnline->id) }}" method="POST" id="formTransaksiOnline">
            @csrf
            @method('PUT')

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Pelanggan</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $transaksiOnline->user_id == $user->id ? 'selected' : '' }}>{{ $user->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal</label>
                    <input type="datetime-local" name="tanggal" class="form-control" value="{{ \Carbon\Carbon::parse($transaksiOnline->tanggal)->format('Y-m-d\TH:i') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="form-select" required>
                        <option value="payment_gateway" {{ $transaksiOnline->metode_pembayaran == 'payment_gateway' ? 'selected' : '' }}>Payment Gateway</option>
                        <option value="cod" {{ $transaksiOnline->metode_pembayaran == 'cod' ? 'selected' : '' }}>COD</option>
                        <option value="bayar_di_toko" {{ $transaksiOnline->metode_pembayaran == 'bayar_di_toko' ? 'selected' : '' }}>Bayar di Toko</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-select" required>
                        <option value="pending" {{ $transaksiOnline->status_pembayaran == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="lunas" {{ $transaksiOnline->status_pembayaran == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="gagal" {{ $transaksiOnline->status_pembayaran == 'gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Transaksi</label>
                    <select name="status_transaksi" class="form-select" required>
                        <option value="diproses" {{ $transaksiOnline->status_transaksi == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="diantar" {{ $transaksiOnline->status_transaksi == 'diantar' ? 'selected' : '' }}>Diantar</option>
                        <option value="diambil" {{ $transaksiOnline->status_transaksi == 'diambil' ? 'selected' : '' }}>Diambil</option>
                        <option value="selesai" {{ $transaksiOnline->status_transaksi == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="batal" {{ $transaksiOnline->status_transaksi == 'batal' ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ambil di Toko?</label>
                    <select name="diambil_di_toko" class="form-select" required>
                        <option value="0" {{ !$transaksiOnline->diambil_di_toko ? 'selected' : '' }}>Tidak</option>
                        <option value="1" {{ $transaksiOnline->diambil_di_toko ? 'selected' : '' }}>Ya</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Alamat Pengambilan / Pengiriman</label>
                    <textarea name="alamat_pengambilan" class="form-control" rows="2">{{ $transaksiOnline->alamat_pengambilan }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="2">{{ $transaksiOnline->catatan }}</textarea>
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
                            @foreach ($transaksiOnline->detail as $detail)
                            <tr class="product-row">
                                <td>
                                    <select name="produk_id[]" class="form-select produk-select" required>
                                        <option value="">Pilih Produk</option>
                                        @foreach ($produks as $item)
                                        <option value="{{ $item->id }}"
                                            data-satuan='@json($item->satuans)'
                                            data-harga='@json($item->hargaProduks->pluck("harga", "satuan_id"))'
                                            {{ $detail->produk_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_produk }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="satuan_id[]" class="form-select satuan-select" required>
                                        @foreach ($detail->produk->satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ $detail->satuan_id == $satuan->id ? 'selected' : '' }}>
                                            {{ $satuan->nama_satuan }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="harga[]" class="form-control harga text-end"
                                        value="{{ number_format($detail->harga, 0, ',', '.') }}" readonly>
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control jumlah" min="0.01" step="0.01"
                                        value="{{ $detail->jumlah }}" required>
                                </td>
                                <td>
                                    <input type="text" name="subtotal[]" class="form-control subtotal text-end"
                                        value="{{ number_format($detail->subtotal, 0, ',', '.') }}" readonly>
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
                    <div class="text-end mt-2 fw-bold">
                        <span id="totalDisplay">Rp {{ number_format($transaksiOnline->total, 0, ',', '.') }}</span>
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
@include('transaksi_online.form_script')
@endsection