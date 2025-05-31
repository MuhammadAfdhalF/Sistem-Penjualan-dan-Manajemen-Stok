<div class="container">
    <h4 class="mb-2 fw-bold">📦 ROP (Reorder Point)</h4>
    <hr style="border: 0; height: 8px; background-color: rgb(0, 0, 0); margin-bottom: 24px;" />

    <div class="table-responsive w-100 overflow-x-auto">
        <table class="table table-bordered table-striped min-w-[800px]">
            <thead style="background-color: rgba(112, 218, 250, 0.75); color: #000;">
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
                @forelse ($produk as $item)
                <tr class="{{ $item->isStokDiBawahROP() ? 'table-danger' : '' }}">
                    <td class="py-2 px-2">{{ $item->nama_produk }}</td>
                    <td class="py-2 px-2">{{ $item->stok_bertingkat }}</td>
                    <td class="py-2 px-2">{{ number_format($item->rop, 2) }}</td>
                    <td class="py-2 px-2">{{ $item->lead_time ?? '-' }}</td>
                    <td class="py-2 px-2">{{ $item->daily_usage !== null ? number_format($item->daily_usage, 2) : '-' }}</td>
                    <td class="py-2 px-2">{{ $item->safety_stock !== null ? number_format($item->safety_stock, 2) : '-' }}</td>
                    <td class="py-2 px-2">
                        @if ($item->isStokDiBawahROP())
                        <span class="badge bg-danger">
                            🔴 Butuh Reorder Min:
                            {{ $item->tampilkanStok3Tingkatan(max(1, $item->rop - $item->stok)) }}
                        </span>
                        @else
                        <span class="badge bg-success">🟢 Stok Aman</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Tidak ada data produk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>