<div class="container">
    <h4 class="mb-2 fw-bold" style="font-weight: 700;">📈 Ringkasan Keuangan Bulanan</h4>
    <hr style="border: 0; height: 6px; background-color: rgb(0, 0, 0); margin-bottom: 20px;" />

    {{-- Baris atas: 2 chart (pemasukan dan pengeluaran) --}}
    <div class="d-flex justify-content-center gap-4 flex-wrap">
        <!-- Pemasukan -->
        <div class="shadow-sm bg-white rounded p-4"
            style="flex: 1 1 320px; max-width: 500px; min-width: 300px; position: relative; height: 290px;">
            <div style="position: absolute; top: 12px; right: 16px;">
                <select id="select-pemasukan" style="padding: 4px 8px; font-size: 14px;">
                    @for ($tahun = $tahunSekarang; $tahun >= $tahunMulai; $tahun--)
                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endfor
                </select>
            </div>
            <h6 class="text-center fw-semibold mb-3">Total Pemasukan per Bulan</h6>
            <canvas id="chart-pemasukan" height="180"></canvas>
        </div>

        <!-- Pengeluaran -->
        <div class="shadow-sm bg-white rounded p-4"
            style="flex: 1 1 320px; max-width: 500px; min-width: 300px; position: relative; height: 290px;">
            <div style="position: absolute; top: 12px; right: 16px;">
                <select id="select-pengeluaran" style="padding: 4px 8px; font-size: 14px;">
                    @for ($tahun = $tahunSekarang; $tahun >= $tahunMulai; $tahun--)
                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endfor
                </select>
            </div>
            <h6 class="text-center fw-semibold mb-3">Total Pengeluaran per Bulan</h6>
            <canvas id="chart-pengeluaran" height="180"></canvas>
        </div>
    </div>

    {{-- Baris bawah: Pendapatan Bersih di tengah --}}
    <div class="d-flex justify-content-center mt-4">
        <div class="shadow-sm bg-white rounded p-4"
            style="width: 100%; max-width: 540px; position: relative; height: 290px;">
            <div style="position: absolute; top: 12px; right: 16px;">
                <select id="select-bersih" style="padding: 4px 8px; font-size: 14px;">
                    @for ($tahun = $tahunSekarang; $tahun >= $tahunMulai; $tahun--)
                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                    @endfor
                </select>
            </div>
            <h6 class="text-center fw-semibold mb-3">Total Pendapatan Bersih per Bulan</h6>
            <canvas id="chart-bersih" height="180"></canvas>
        </div>
    </div>
</div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    const chartInstances = {
        pemasukan: null,
        pengeluaran: null,
        bersih: null,
    };

    function fetchAndRenderChart(tipe, tahun) {
        fetch(`/api/grafik/keuangan/${tipe}/${tahun}`)
            .then(res => res.json())
            .then(data => {
                const canvasId = 'chart-' + tipe;
                const ctx = document.getElementById(canvasId).getContext('2d');

                if (chartInstances[tipe]) chartInstances[tipe].destroy();

                chartInstances[tipe] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: bulanLabels,
                        datasets: [{
                            label: `Total ${tipe}`,
                            data: data,
                            borderColor: tipe === 'pemasukan' ? '#28a745' : (tipe === 'pengeluaran' ? '#dc3545' : '#007bff'),
                            backgroundColor: '#ffffff00',
                            tension: 0.3,
                            pointRadius: 4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: v => 'Rp ' + v.toLocaleString('id-ID')
                                }
                            }
                        }
                    }
                });
            });
    }

    // Inisialisasi grafik awal tahun sekarang
    const currentYear = new Date().getFullYear();
    fetchAndRenderChart('pemasukan', currentYear);
    fetchAndRenderChart('pengeluaran', currentYear);
    fetchAndRenderChart('bersih', currentYear);

    // Event listener per dropdown
    document.getElementById('select-pemasukan').addEventListener('change', function() {
        fetchAndRenderChart('pemasukan', this.value);
    });

    document.getElementById('select-pengeluaran').addEventListener('change', function() {
        fetchAndRenderChart('pengeluaran', this.value);
    });

    document.getElementById('select-bersih').addEventListener('change', function() {
        fetchAndRenderChart('bersih', this.value);
    });
</script>
@endpush