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

            <form method="GET" action="{{ route('keranjang.index') }}"
                class="row row-cols-1 row-cols-md-auto gx-2 gy-1 align-items-end flex-wrap mb-3">

                <div class="col">
                    <label for="filter_date" class="form-label small mb-1">Tanggal</label>
                    <input type="date" id="filter_date" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
                </div>
                <div class="col">
                    <label for="filter_month" class="form-label small mb-1">Bulan</label>
                    <select id="filter_month" name="month" class="form-select form-select-sm" style="min-width: 90px;">
                        <option value="">--</option>
                        @foreach(range(1,12) as $month)
                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($month)->format('M') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="filter_year" class="form-label small mb-1">Tahun</label>
                    <select id="filter_year" name="year" class="form-select form-select-sm" style="min-width: 80px;">
                        <option value="">--</option>
                        @foreach($tahunTersedia as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="filter_user" class="form-label small mb-1">Pelanggan</label>
                    <select id="filter_user" name="user_id" class="form-select form-select-sm" style="min-width: 120px;">
                        <option value="">-- Semua --</option>
                        @foreach($daftarPelanggan as $p)
                        <option value="{{ $p->id }}" {{ request('user_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col d-flex gap-1 align-items-end">
                    <button type="submit" class="btn btn-primary btn-xs px-2 py-1" style="font-size: 0.8rem;">
                        <i class="ti ti-filter"></i>
                    </button>
                    <a href="{{ route('keranjang.index') }}" class="btn btn-secondary btn-xs px-2 py-1" style="font-size: 0.8rem;">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>


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