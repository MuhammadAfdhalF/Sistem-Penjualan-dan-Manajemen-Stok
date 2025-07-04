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
                            borderColor: tipe === 'pemasukan' ? '#34D399' : (tipe === 'pengeluaran' ? '#EF4444' : '#60A5FA'),
                            backgroundColor: (context) => {
                                const chart = context.chart;
                                const { ctx, chartArea } = chart;
                                if (!chartArea) {
                                    return null;
                                }
                                const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                                const color = tipe === 'pemasukan' ? '#34D399' : (tipe === 'pengeluaran' ? '#EF4444' : '#60A5FA');
                                gradient.addColorStop(0, `${color}00`);
                                gradient.addColorStop(0.5, `${color}40`);
                                gradient.addColorStop(1, `${color}80`);
                                return gradient;
                            },
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: tipe === 'pemasukan' ? '#34D399' : (tipe === 'pengeluaran' ? '#EF4444' : '#60A5FA'),
                            pointBorderColor: '#fff',
                            pointHoverRadius: 7,
                            pointHoverBackgroundColor: tipe === 'pemasukan' ? '#22C55E' : (tipe === 'pengeluaran' ? '#DC2626' : '#2563EB'),
                            pointHoverBorderColor: '#fff',
                            fill: true,
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1000,
                            easing: 'easeInOutQuart'
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#333',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: '#666',
                                borderWidth: 1,
                                cornerRadius: 4,
                                displayColors: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.08)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: '#666',
                                    callback: v => 'Rp ' + v.toLocaleString('id-ID')
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.08)',
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: '#666',
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