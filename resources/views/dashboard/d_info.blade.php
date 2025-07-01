<div>
    <h4 class="mb-2 fw-bold" style="font-weight: 700;">ℹ️ Informasi</h4>
    <hr style="border: 0; height: 6px; background-color: rgb(0, 0, 0); margin-bottom: 20px;" />

    <div class="d-flex flex-wrap gap-3 justify-content-center">

        @php
        $cardStyle = 'width: 140px; height: 115px; background-color: white;';
        @endphp

        <!-- Kartu Toko Kecil -->
        <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center shadow-sm"
            style="{{ $cardStyle }}">
            <div style="font-size: 32px; margin-bottom: 6px;">🏪</div>
            <strong style="font-weight: 600; font-size: 14px; text-align: center;">Toko Kecil</strong>
            <small style="color: #555;">{{ $totalToko }} Toko</small>
        </div>

        <!-- Kartu Individu -->
        <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center shadow-sm"
            style="{{ $cardStyle }}">
            <div style="font-size: 32px; margin-bottom: 6px;">👥</div>
            <strong style="font-weight: 600; font-size: 14px; text-align: center;">Individu</strong>
            <small style="color: #555;">{{ $totalIndividu }} Orang</small>
        </div>

        <!-- Kartu Total Produk -->
        <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center shadow-sm"
            style="{{ $cardStyle }}">
            <div style="font-size: 32px; margin-bottom: 6px;">📦</div>
            <strong style="font-weight: 600; font-size: 14px; text-align: center;">Total Produk</strong>
            <small style="color: #555;">{{ $totalProduk }} Produk</small>
        </div>

        <!-- Kartu Pemasukan -->
        <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center shadow-sm"
            style="{{ $cardStyle }}">
            <div style="font-size: 32px; margin-bottom: 6px;">💰</div>
            <strong style="font-weight: 600; font-size: 14px; text-align: center;">Pemasukan Bulan Ini</strong>
            <small style="color: #555;">Rp {{ number_format($totalPemasukanBulanIni, 0, ',', '.') }}</small>
        </div>

        <!-- Kartu Pengeluaran -->
        <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center shadow-sm"
            style="{{ $cardStyle }}">
            <div style="font-size: 32px; margin-bottom: 6px;">📉</div>
            <strong style="font-weight: 600; font-size: 14px; text-align: center;">Pengeluaran Bulan Ini</strong>
            <small style="color: #555;">Rp {{ number_format($totalPengeluaranBulanIni, 0, ',', '.') }}</small>
        </div>

        <!-- Kartu Pendapatan Bersih -->
        <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center shadow-sm"
            style="{{ $cardStyle }}">
            <div style="font-size: 32px; margin-bottom: 6px;">📊</div>
            <strong style="font-weight: 600; font-size: 14px; text-align: center;">Pendapatan Bersih Bulan Ini</strong>
            <small style="color: #555;">Rp {{ number_format($totalPendapatanBersih, 0, ',', '.') }}</small>
        </div>

        @php
        $today = now()->format('Y-m-d');
        $pemasukanHariIni = \App\Models\Keuangan::where('jenis', 'pemasukan')->whereDate('tanggal', $today)->sum('nominal');
        $pengeluaranHariIni = \App\Models\Keuangan::where('jenis', 'pengeluaran')->whereDate('tanggal', $today)->sum('nominal');
        $pendapatanBersihHariIni = $pemasukanHariIni - $pengeluaranHariIni;
        @endphp

        <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
            <!-- Kartu Pemasukan Hari Ini -->
            <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center shadow-sm"
                style="{{ $cardStyle }}">
                <div style="font-size: 32px; margin-bottom: 6px;">📥</div>
                <strong style="font-weight: 600; font-size: 14px; text-align: center;">Pemasukan Hari Ini</strong>
                <small style="color: #555;">Rp {{ number_format($pemasukanHariIni, 0, ',', '.') }}</small>
            </div>

            <!-- Kartu Pengeluaran Hari Ini -->
            <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center shadow-sm"
                style="{{ $cardStyle }}">
                <div style="font-size: 32px; margin-bottom: 6px;">📤</div>
                <strong style="font-weight: 600; font-size: 14px; text-align: center;">Pengeluaran Hari Ini</strong>
                <small style="color: #555;">Rp {{ number_format($pengeluaranHariIni, 0, ',', '.') }}</small>
            </div>

            <!-- Kartu Pendapatan Bersih Hari Ini -->
            <div class="border rounded p-3 d-flex flex-column align-items-center justify-content-center shadow-sm"
                style="{{ $cardStyle }}">
                <div style="font-size: 32px; margin-bottom: 6px;">📈</div>
                <strong style="font-weight: 600; font-size: 14px; text-align: center;">Pendapatan Bersih Hari Ini</strong>
                <small style="color: #555;">Rp {{ number_format($pendapatanBersihHariIni, 0, ',', '.') }}</small>
            </div>
        </div>

    </div>

    <div class="row mt-4">
        <!-- Stok Kurang -->
        <div class="col-md-6 mb-3">
            <div class="border rounded p-3 shadow-sm bg-white h-100">
                <div class="d-flex align-items-center mb-2">
                    <div style="font-size: 24px; margin-right: 10px;">⚠️</div>
                    <h5 class="mb-0 fw-bold">Stok Kurang (Butuh Reorder)</h5>
                </div>

                @if($produkMenipis->count() > 0)
                <ul class="mb-0 ps-3" style="font-size: 14px; line-height: 1.5;">
                    @foreach($produkMenipis->take(5) as $item)
                    <li>
                        <strong>{{ $item->nama_produk }}</strong> →
                        <span class="text-danger">butuh reorder {{ $item->tampilkanStok3Tingkatan(max(1, $item->rop - $item->stok)) }}</span>
                    </li>
                    @endforeach
                    @if($produkMenipis->count() > 5)
                    <li><em>+{{ $produkMenipis->count() - 5 }} produk lainnya</em></li>
                    @endif
                </ul>
                @else
                <div class="text-success" style="font-size: 14px;">✅ Semua stok dalam kondisi aman</div>
                @endif
            </div>
        </div>

        <!-- Produk Terlaris -->
        <div class="col-md-6 mb-3">
            <div class="border rounded p-3 shadow-sm bg-white h-100">
                <div class="d-flex align-items-center mb-2">
                    <div style="font-size: 24px; margin-right: 10px;">🔥</div>
                    <h5 class="mb-0 fw-bold">5 Produk Terlaris</h5>
                </div>

                @if($produkTerlaris->count())
                <ul class="mb-0 ps-3" style="font-size: 14px; line-height: 1.5;">
                    @foreach($produkTerlaris as $item)
                    <li>
                        <strong>{{ $item['nama'] }}</strong> → {{ $item['total'] }} terjual
                    </li>
                    @endforeach
                </ul>

                @else
                <div class="text-muted" style="font-size: 14px;">Belum ada data penjualan</div>
                @endif
            </div>
        </div>

        <div class="row mt-4">
            <!-- Pelanggan Terroyal: Individu -->
            <div class="col-md-6 mb-3">
                <div class="border rounded p-3 shadow-sm bg-white h-100">
                    <div class="d-flex align-items-center mb-2">
                        <div style="font-size: 24px; margin-right: 10px;">👤</div>
                        <h5 class="mb-0 fw-bold">Top 5 Individu Terroyal</h5>
                    </div>

                    @if($terroyalIndividu->count())
                    <ul class="mb-0 ps-3" style="font-size: 14px; line-height: 1.5;">
                        @foreach($terroyalIndividu as $item)
                        <li>
                            <strong>{{ $item->nama }}</strong> → Rp {{ number_format($item->total_belanja, 0, ',', '.') }}
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-muted" style="font-size: 14px;">Belum ada transaksi individu</div>
                    @endif
                </div>
            </div>

            <!-- Pelanggan Terroyal: Toko Kecil -->
            <div class="col-md-6 mb-3">
                <div class="border rounded p-3 shadow-sm bg-white h-100">
                    <div class="d-flex align-items-center mb-2">
                        <div style="font-size: 24px; margin-right: 10px;">🏪</div>
                        <h5 class="mb-0 fw-bold">Top 5 Toko Kecil Terroyal</h5>
                    </div>

                    @if($terroyalToko->count())
                    <ul class="mb-0 ps-3" style="font-size: 14px; line-height: 1.5;">
                        @foreach($terroyalToko as $item)
                        <li>
                            <strong>{{ $item->nama }}</strong> → Rp {{ number_format($item->total_belanja, 0, ',', '.') }}
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-muted" style="font-size: 14px;">Belum ada transaksi toko kecil</div>
                    @endif
                </div>
            </div>
        </div>


    </div>

</div>