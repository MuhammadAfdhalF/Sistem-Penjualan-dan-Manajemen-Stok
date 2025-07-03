@extends('layouts.mantis')

@section('title', 'Detail Transaksi Offline')

<head>
    <title>Halaman Detail Transaksi Offline</title>
</head>

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">Detail Transaksi</h4>
        <a href="{{ route('transaksi_offline.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <p><strong>Kode Transaksi:</strong> {{ $transaksi->kode_transaksi }}</p>
        <p><strong>Nama Pelanggan:</strong> {{ $transaksi->pelanggan?->nama ?? 'Bukan Member' }}</p>
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaksi->tanggal)->format('d-m-Y H:i') }}</p>
        <p>
            <strong>Metode Pembayaran:</strong>
            @if ($transaksi->metode_pembayaran === 'payment_gateway')
            {{ ucwords(str_replace('_', ' ', $transaksi->metode_pembayaran)) }}
            @if ($transaksi->payment_type)
            <br><small>({{ ucwords(str_replace('_', ' ', $transaksi->payment_type)) }})</small>
            @endif
            @else
            {{ ucwords(str_replace('_', ' ', $transaksi->metode_pembayaran)) }}
            @endif
        </p>
        <p>
            <strong>Status Pembayaran:</strong>
            @php
            $badgeClass = '';
            switch ($transaksi->status_pembayaran) {
            case 'lunas':
            $badgeClass = 'bg-success';
            break;
            case 'pending':
            $badgeClass = 'bg-warning';
            break;
            case 'gagal':
            case 'expire':
            $badgeClass = 'bg-danger';
            break;
            default:
            $badgeClass = 'bg-secondary';
            break;
            }
            @endphp
            <span class="badge {{ $badgeClass }}">{{ ucwords($transaksi->status_pembayaran) }}</span>
        </p>


        <h5 class="mt-4">Detail Produk</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 30%;">Nama Produk</th>
                    <th style="width: 20%;">Jumlah</th>
                    <th style="width: 20%;">Harga per Satuan</th>
                    <th style="width: 25%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaksi->detail as $index => $item)
                @php
                $jumlahArr = is_array($item->jumlah_json) ? $item->jumlah_json : json_decode($item->jumlah_json, true);
                $hargaArr = is_array($item->harga_json) ? $item->harga_json : json_decode($item->harga_json, true);
                $filteredJumlahArr = collect($jumlahArr)->filter(fn($qty) => $qty > 0)->toArray();
                $jumlahCount = count($filteredJumlahArr);
                $firstRow = true;
                @endphp

                @if($jumlahCount > 0)
                @foreach ($filteredJumlahArr as $satuanId => $qty)
                @php
                $satuan = \App\Models\Satuan::find($satuanId);
                $hargaSatuan = $hargaArr[$satuanId] ?? 0;
                @endphp
                <tr>
                    @if($firstRow)
                    <td rowspan="{{ $jumlahCount }}">{{ $index + 1 }}</td>
                    <td rowspan="{{ $jumlahCount }}">{{ $item->produk->nama_produk ?? '-' }}</td>
                    @endif

                    <td>{{ $qty }} {{ $satuan?->nama_satuan ?? 'Satuan tidak ditemukan' }}</td>
                    <td>Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>

                    @if($firstRow)
                    <td rowspan="{{ $jumlahCount }}">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    @endif
                </tr>
                @php $firstRow = false; @endphp
                @endforeach
                @else
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->produk->nama_produk ?? '-' }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end"><strong>Total</strong></td>
                    <td><strong>Rp {{ number_format($transaksi->total, 0, ',', '.') }}</strong></td>
                </tr>
                {{-- Sembunyikan Dibayar dan Kembalian jika metode pembayaran adalah payment_gateway --}}
                @if ($transaksi->metode_pembayaran !== 'payment_gateway')
                <tr>
                    <td colspan="4" class="text-end"><strong>Dibayar</strong></td>
                    <td><strong>Rp {{ number_format($transaksi->dibayar, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><strong>Kembalian</strong></td>
                    <td><strong>Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</strong></td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>
@endsection