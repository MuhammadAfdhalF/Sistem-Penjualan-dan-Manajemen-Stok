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
                        $satuanMap = $item->produk->satuans->keyBy('id');
                        $hargaMap = $item->produk->hargaProduks
                        ->where('jenis_pelanggan', $jenis)
                        ->pluck('harga', 'satuan_id');
                        $jumlahArr = is_array($item->jumlah_json) ? $item->jumlah_json : json_decode($item->jumlah_json, true);
                        if (!is_array($jumlahArr)) $jumlahArr = [];
                        if (is_int($jumlahArr)) $jumlahArr = [];
                        if (array_values($jumlahArr) === $jumlahArr) {
                        $newArr = [];
                        foreach ($jumlahArr as $val) {
                        if (is_array($val) && isset($val['satuan_id']) && isset($val['jumlah'])) {
                        $newArr[$val['satuan_id']] = $val['jumlah'];
                        }
                        }
                        $jumlahArr = $newArr;
                        }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->produk->nama_produk ?? 'Produk tidak ditemukan' }}</td>
                            <td>
                                <form method="POST" action="{{ route('keranjang.update', $item->id) }}" class="inline-update-form" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <div class="d-flex flex-wrap gap-1 align-items-center">
                                        @foreach ($satuanMap as $sid => $satuan)
                                        <div style="min-width:90px;">
                                            <label style="font-size:90%;">{{ $satuan->nama_satuan }}</label>
                                            <input
                                                type="number"
                                                step="1"
                                                min="0"
                                                pattern="\d*"
                                                class="form-control form-control-sm jumlah-input"
                                                name="jumlah_json[{{ $sid }}]"
                                                value="{{ isset($jumlahArr[$sid]) ? intval($jumlahArr[$sid]) : '' }}"
                                                style="width:60px;display:inline-block;"
                                                data-harga="{{ $hargaMap[$sid] ?? 0 }}" />
                                        </div>
                                        @endforeach

                                        <!-- Tombol ceklis ada di flex row, beri sedikit margin kiri -->
                                        <button type="submit"
                                            class="btn btn-success btn-sm p-0 ms-1"
                                            title="Simpan"
                                            style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ti ti-check" style="font-size: 14px;"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" class="subtotal-input" value="">
                                </form>
                            </td>

                            <td>
                                <span class="subtotal-view"></span>
                            </td>
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

@section('scripts')
<script>
    document.querySelectorAll('.inline-update-form').forEach(form => {
        const updateSubtotal = () => {
            let subtotal = 0;
            form.querySelectorAll('.jumlah-input').forEach(inp => {
                const harga = parseFloat(inp.dataset.harga) || 0;
                const qty = parseInt(inp.value) || 0;
                subtotal += harga * qty;
            });
            const subtotalStr = subtotal ? 'Rp ' + subtotal.toLocaleString('id-ID') : '-';
            form.closest('tr').querySelector('.subtotal-view').innerText = subtotalStr;
            form.querySelector('.subtotal-input').value = subtotal;
        };
        updateSubtotal();
        form.querySelectorAll('.jumlah-input').forEach(inp => {
            inp.addEventListener('input', updateSubtotal);
        });
    });
</script>
@endsection