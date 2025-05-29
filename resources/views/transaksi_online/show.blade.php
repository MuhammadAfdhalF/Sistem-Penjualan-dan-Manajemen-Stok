@extends('layouts.mantis')

@section('title')
Detail Transaksi Online
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Detail Transaksi</h4>
        <a href="{{ route('transaksi_online.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <p><strong>Kode Transaksi:</strong> {{ $transaksiOnline->kode_transaksi }}</p>
        <p><strong>Pelanggan:</strong> {{ $transaksiOnline->user->nama ?? '-' }}</p>
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaksiOnline->tanggal)->format('d-m-Y H:i') }}</p>
        <p><strong>Metode Pembayaran:</strong> {{ ucfirst(str_replace('_', ' ', $transaksiOnline->metode_pembayaran)) }}</p>
        <p><strong>Status Pembayaran:</strong> {{ ucfirst($transaksiOnline->status_pembayaran) }}</p>
        <p><strong>Status Transaksi:</strong> {{ ucfirst($transaksiOnline->status_transaksi) }}</p>
        <p><strong>Diambil di Toko:</strong> {{ $transaksiOnline->diambil_di_toko ? 'Ya' : 'Tidak' }}</p>
        <p><strong>Alamat Pengambilan / Pengiriman:</strong> {{ $transaksiOnline->alamat_pengambilan ?? '-' }}</p>
        <p><strong>Catatan:</strong> {{ $transaksiOnline->catatan ?? '-' }}</p>

        <h5 class="mt-4">Detail Produk</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 30%;">Nama Produk</th>
                        <th style="width: 20%;">Jumlah Bertingkat (per satuan)</th>
                        <th style="width: 20%;">Harga per Satuan</th>
                        <th style="width: 25%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    use App\Models\Satuan;
                    use App\Models\HargaProduk;
                    $totalTransaksi = 0;
                    @endphp
                    @foreach ($transaksiOnline->detail as $index => $item)
                    @php
                    $jumlahArr = is_array($item->jumlah_json) ? $item->jumlah_json : json_decode($item->jumlah_json, true);
                    $filteredJumlahArr = collect($jumlahArr)->filter(fn($qty) => $qty > 0)->toArray();
                    $jumlahCount = count($filteredJumlahArr);
                    $firstRow = true;
                    $subtotalItem = 0;
                    @endphp

                    @if($jumlahCount > 0)
                    @foreach ($filteredJumlahArr as $satuanId => $qty)
                    @php
                    $satuan = Satuan::find($satuanId);
                    $hargaSatuan = HargaProduk::where('produk_id', $item->produk_id)
                    ->where('satuan_id', $satuanId)
                    ->where('jenis_pelanggan', $transaksiOnline->user->jenis_pelanggan ?? 'Individu')
                    ->value('harga') ?? 0;
                    $subtotalSatuan = $hargaSatuan * $qty;
                    $subtotalItem += $subtotalSatuan;
                    @endphp
                    <tr>
                        @if($firstRow)
                        <td rowspan="{{ $jumlahCount }}">{{ $index + 1 }}</td>
                        <td rowspan="{{ $jumlahCount }}">{{ $item->produk->nama_produk ?? '-' }}</td>
                        @endif

                        <td>{{ $qty }} {{ $satuan?->nama_satuan ?? 'Satuan tidak ditemukan' }}</td>
                        <td>Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>

                        @if($firstRow)
                        <td rowspan="{{ $jumlahCount }}">Rp {{ number_format($subtotalItem, 0, ',', '.') }}</td>
                        @endif
                    </tr>
                    @php $firstRow = false; @endphp
                    @endforeach
                    @php $totalTransaksi += $subtotalItem; @endphp
                    @else
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->produk->nama_produk ?? '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>Rp 0</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total</strong></td>
                        <td><strong>Rp {{ number_format($totalTransaksi, 0, ',', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection