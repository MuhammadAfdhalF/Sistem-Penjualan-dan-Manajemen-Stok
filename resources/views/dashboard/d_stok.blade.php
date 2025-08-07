<div class="relative w-full"> {{-- Tambahkan 'w-full' di sini untuk memastikan lebar penuh --}}
    <h4 class="mb-2 fw-bold">📦 ROP (Reorder Point)</h4>

    <div class="relative w-full"> <!-- Elemen induk yang relatif -->
        <!-- Tombol Update ROP di sudut kanan atas -->
        <div class="absolute top-0 right-0 z-10">
            <button id="updateRopBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Update ROP
            </button>
            <div id="updateStatus" class="mt-2 p-2 bg-gray-100 text-gray-800 text-sm rounded-md shadow-sm hidden text-center"></div>
        </div>
    </div>

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
                @php
                // Urutkan produk: butuh reorder di atas, stok paling sedikit di atas
                $produkTerurut = $produk->sortBy(function($item) {
                return [
                $item->isStokDiBawahROP() ? 0 : 1, // Prioritas: butuh reorder
                $item->stok // Urut stok dari sedikit ke banyak
                ];
                });
                @endphp

                @forelse ($produkTerurut as $item)
                <tr class="{{ $item->isStokDiBawahROP() ? 'table-danger' : '' }}">
                    <td class="py-2 px-2">{{ $item->nama_produk }}</td>
                    <td class="py-2 px-2">
                        {{ $item->stok_bertingkat }}<br>({{ (int) $item->stok }})
                    </td>
                    <td class="py-2 px-2">
                        {{ $item->tampilkanStok3Tingkatan(round($item->rop)) }}<br>
                        <span class="text-muted">({{ number_format($item->rop, 2) }})</span>
                    </td>
                    <td class="py-2 px-2">{{ $item->lead_time ?? '-' }}</td>
                    <td class="py-2 px-2">
                        {{ $item->daily_usage !== null ? number_format($item->daily_usage, 2) : '-' }}
                    </td>
                    <td class="py-2 px-2">
                        {{ $item->safety_stock !== null ? number_format($item->safety_stock, 2) : '-' }}
                    </td>
                    <td class="py-2 px-2">
                        @if ($item->isStokDiBawahROP())
                        <span class="badge bg-danger">
                            🔴 Butuh Reorder Min:&nbsp;
                            {{ $item->tampilkanStok3Tingkatan(max(1, ceil($item->rop - $item->stok))) }}
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateRopBtn = document.getElementById('updateRopBtn');
        const updateStatus = document.getElementById('updateStatus');

        if (updateRopBtn && updateStatus) {
            updateRopBtn.addEventListener('click', function() {
                updateRopBtn.disabled = true;
                updateRopBtn.textContent = 'Memperbarui ROP...';
                updateStatus.classList.remove('hidden');
                updateStatus.textContent = 'Memulai proses pembaruan...';
                updateStatus.style.color = '#333';
                updateStatus.style.backgroundColor = '#e0e0e0';

                setTimeout(() => {
                    updateStatus.textContent = 'ROP berhasil diperbarui!';
                    updateStatus.style.color = 'green';
                    updateStatus.style.backgroundColor = '#d4edda';

                    setTimeout(() => {
                        updateRopBtn.disabled = false;
                        updateRopBtn.textContent = 'Update ROP';
                        updateStatus.classList.add('hidden');
                        updateStatus.style.backgroundColor = '';
                    }, 1000);
                }, 1000);
            });
        }
    });
</script>