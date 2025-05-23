<div class="container">
    <h4 class="mb-2 fw-bold" style="font-weight: 700;">ROP</h4>
    <hr style="border: 0; height: 8px; background-color:rgb(0, 0, 0); width: 100%; margin-left: 0; margin-bottom: 24px;" />

    <table class="table table-bordered table-striped">
        <thead class="" style="background-color:rgba(112, 218, 250, 0.75); color: #000;">
            <tr>
                <th>Nama Produk</th>
                <th>Stok</th>
                <th>ROP</th>
                <th>Lead Time (hari)</th>
                <th>Daily Usage</th>
                <th>Safety Stock</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($produk as $produk)
            <tr class="{{ $produk->isStokDiBawahROP() ? 'table-danger' : '' }}">
                <td>{{ $produk->nama_produk }}</td>
                <td>{{ $produk->stok_bertingkat }}</td>
                <td>{{ $produk->rop }}</td>
                <td>{{ $produk->lead_time ?? '-' }}</td>
                <td>{{ $produk->daily_usage !== null ? number_format($produk->daily_usage, 2) : '-' }}</td>
                <td>{{ $produk->safety_stock !== null ? number_format($produk->safety_stock, 2) : '-' }}</td>
                <td>
                    @if($produk->isStokDiBawahROP())
                    <span class="badge bg-danger">
                        Butuh Reorder Min: {{ $produk->tampilkanStok3Tingkatan(max(0, ($produk->rop - $produk->stok) + 1)) }}
                    </span>
                    @else
                    <span class="badge bg-success">Stok Aman</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data produk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>