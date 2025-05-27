@extends('layouts.mantis')

@section('title')
Keranjang Pelanggan - Admin View
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Keranjang Pelanggan</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pelanggan</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Tanggal Ditambahkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keranjangs as $index => $item)
                        @php
                        // Mapping satuan dari produk
                        $satuanMap = $item->produk->satuans->keyBy('id');

                        // Jenis pelanggan untuk user ini
                        $jenisPelanggan = $item->user->jenis_pelanggan ?? 'Individu';

                        // Harga per satuan sesuai jenis pelanggan
                        $hargaMap = $item->produk->hargaProduks
                        ->where('jenis_pelanggan', $jenisPelanggan)
                        ->pluck('harga', 'satuan_id');

                        // Decode jumlah_json (pastikan array asosiatif)
                        $jumlahArr = is_array($item->jumlah_json) ? $item->jumlah_json : json_decode($item->jumlah_json, true);
                        if (!is_array($jumlahArr)) $jumlahArr = [];
                        if (is_int($jumlahArr)) $jumlahArr = [];

                        // Fallback legacy: array numerik
                        if (array_values($jumlahArr) === $jumlahArr) {
                        $newArr = [];
                        foreach ($jumlahArr as $val) {
                        if (is_array($val) && isset($val['satuan_id']) && isset($val['jumlah'])) {
                        $newArr[$val['satuan_id']] = $val['jumlah'];
                        }
                        }
                        $jumlahArr = $newArr;
                        }

                        // Format jumlah tampil
                        $jumlahString = collect($jumlahArr)
                        ->filter(fn($qty, $sid) => is_numeric($qty) && $qty > 0)
                        ->map(fn($qty, $sid) => $qty . ' ' . ($satuanMap[$sid]->nama_satuan ?? ''))
                        ->join(', ');

                        // Hitung subtotal sesuai harga jenis pelanggan
                        $subtotal = collect($jumlahArr)
                        ->filter(fn($qty, $sid) => is_numeric($qty) && $qty > 0)
                        ->map(fn($qty, $sid) => $qty * ($hargaMap[$sid] ?? 0))
                        ->sum();
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->user->nama ?? 'Tidak Diketahui' }}</td>
                            <td>{{ $item->produk->nama_produk ?? 'Produk tidak ditemukan' }}</td>
                            <td>{{ $jumlahString ?: '-' }}</td>
                            <td>{{ $subtotal ? 'Rp ' . number_format($subtotal, 0, ',', '.') : '-' }}</td>
                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection