@extends('layouts.mantis')

@section('title')
Keranjang Saya
@endsection

@section('content')
<div class="">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Keranjang Saya</h4>
            <a href="{{ route('keranjang.create') }}" class="btn btn-primary btn-sm">
                + Tambah Keranjang
            </a>
        </div>
        <div class="card-body">
            @if ($keranjangs->isEmpty())
            <p>Keranjang kamu masih kosong.</p>
            @else
            <div class="table-responsive">
                <table class="table table-bordered" id="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Tanggal Ditambahkan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keranjangs as $index => $item)
                        @php
                        // Ambil mapping satuan & harga sesuai jenis pelanggan
                        $satuanMap = $item->produk->satuans->keyBy('id');
                        $hargaMap = $item->produk->hargaProduks
                        ->where('jenis_pelanggan', $jenis)
                        ->pluck('harga', 'satuan_id');
                        // Parse jumlah_json
                        $jumlahArr = is_array($item->jumlah_json) ? $item->jumlah_json : json_decode($item->jumlah_json, true);
                        if (!is_array($jumlahArr)) $jumlahArr = [];
                        if (is_int($jumlahArr)) $jumlahArr = [];
                        // Fallback legacy jika numerik
                        if (array_values($jumlahArr) === $jumlahArr) {
                        $newArr = [];
                        foreach ($jumlahArr as $val) {
                        if (is_array($val) && isset($val['satuan_id']) && isset($val['jumlah'])) {
                        $newArr[$val['satuan_id']] = $val['jumlah'];
                        }
                        }
                        $jumlahArr = $newArr;
                        }
                        // String jumlah
                        $jumlahString = collect($jumlahArr)
                        ->filter(fn($qty, $sid) => is_numeric($qty) && $qty > 0)
                        ->map(fn($qty, $sid) => $qty . ' ' . ($satuanMap[$sid]->nama_satuan ?? ''))
                        ->join(', ');
                        // Subtotal sesuai harga jenis pelanggan
                        $subtotal = collect($jumlahArr)
                        ->filter(fn($qty, $sid) => is_numeric($qty) && $qty > 0)
                        ->map(fn($qty, $sid) => floatval($qty) * (floatval($hargaMap[$sid] ?? 0)))
                        ->sum();
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->produk->nama_produk ?? 'Produk tidak ditemukan' }}</td>
                            <td>{{ $jumlahString ?: '-' }}</td>
                            <td>{{ $subtotal ? 'Rp ' . number_format($subtotal, 0, ',', '.') : '-' }}</td>
                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                            <td>
                                <form action="{{ route('keranjang.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus item ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection