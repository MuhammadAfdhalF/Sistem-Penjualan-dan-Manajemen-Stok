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
                            // Ambil satuan dari produk
                            $satuanMap = $item->produk->satuans->keyBy('id');
                            // Ambil harga per satuan
                            $hargaMap = $item->produk->hargaProduks->pluck('harga','satuan_id');
                            // Decode jumlah_json
                            $jumlahArr = is_array($item->jumlah_json) ? $item->jumlah_json : json_decode($item->jumlah_json, true);
                            // String untuk tampilan jumlah bertingkat
                            $jumlahString = collect($jumlahArr)
                                ->filter(fn($j) => ($j['jumlah'] ?? 0) > 0)
                                ->map(fn($j) => ($j['jumlah'] ?? 0) . ' ' . ($satuanMap[$j['satuan_id']]->nama_satuan ?? ''))
                                ->join(', ');
                            // Subtotal
                            $subtotal = collect($jumlahArr)
                                ->map(fn($j) => ($j['jumlah'] ?? 0) * ($hargaMap[$j['satuan_id']] ?? 0))
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
