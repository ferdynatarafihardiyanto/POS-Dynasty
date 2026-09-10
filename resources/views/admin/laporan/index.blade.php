@extends('layouts.pos')

@push('styles')
<style>
    .stat-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background-color: #fff;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.01);
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .icon-purple { background-color: #f3e8ff; color: #9333ea; }
    .icon-blue { background-color: #dbeafe; color: #2563eb; }
    .icon-red { background-color: #fef2f2; color: #dc2626; }
    .icon-green { background-color: #dcfce7; color: #16a34a; }
    
    .tab-btn {
        border: 1px solid #e5e7eb;
        background-color: #fff;
        color: #4b5563;
        font-weight: 600;
        padding: 8px 24px;
        border-radius: 50px;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .tab-btn.active {
        background-color: #8b211e;
        color: white;
        border-color: #8b211e;
    }
    
    .chart-container {
        background-color: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.01);
    }
    
    .chart-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: #111827;
        margin-bottom: 20px;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="pos-main">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between p-3 p-md-4 bg-white border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-light border rounded-3 p-2 d-lg-none shadow-xs d-flex align-items-center justify-content-center" id="openSidebarBtn" title="Buka Navigasi" style="width: 38px; height: 38px;">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h4 class="mb-0 fw-bold text-dark fs-5 fs-md-4">Laporan Penjualan</h4>
                <div class="text-muted small">Lihat ringkasan performa dan unduh laporan</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="border rounded px-3 py-1 text-center bg-light">
                <div class="small text-muted" style="font-size: 0.7rem;">WAKTU</div>
                <div class="fw-bold" id="currentTimeHeader">--:--:-- WIB</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" class="rounded-circle" width="40" height="40" alt="Avatar">
                <div>
                    <div class="fw-bold fs-6 lh-1">{{ Auth::user()->name }}</div>
                    <div class="text-danger small">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-4 overflow-auto flex-grow-1" style="background-color: #fcfcfc;" x-data="{ currentTab: 'ringkasan' }">
        
        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4">
            <div class="dropdown">
                <button class="btn btn-white border bg-white rounded-3 shadow-sm px-4 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-calendar3"></i> 
                    <span class="fw-bold text-dark small">
                        @if($periode == 'today') Hari Ini
                        @elseif($periode == 'week') Minggu Ini
                        @elseif($periode == 'month') Bulan Ini
                        @elseif($periode == 'year') Tahun Ini
                        @endif
                    </span>
                </button>
                <ul class="dropdown-menu shadow-sm border-0 mt-2 rounded-3">
                    <li><a class="dropdown-item py-2 {{ $periode == 'today' ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.laporan.index', ['periode' => 'today']) }}">Hari Ini</a></li>
                    <li><a class="dropdown-item py-2 {{ $periode == 'week' ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.laporan.index', ['periode' => 'week']) }}">Minggu Ini</a></li>
                    <li><a class="dropdown-item py-2 {{ $periode == 'month' ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.laporan.index', ['periode' => 'month']) }}">Bulan Ini</a></li>
                    <li><a class="dropdown-item py-2 {{ $periode == 'year' ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.laporan.index', ['periode' => 'year']) }}">Tahun Ini</a></li>
                </ul>
            </div>
            <button class="btn text-white rounded-3 px-4 d-flex align-items-center gap-2 fw-bold shadow-sm" style="background-color: #8b211e;" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak Laporan
            </button>
        </div>

        <!-- Tabs -->
        <div class="d-flex gap-3 mb-4">
            <button class="tab-btn" :class="{ 'active': currentTab === 'ringkasan' }" @click="currentTab = 'ringkasan'">Ringkasan</button>
            <button class="tab-btn" :class="{ 'active': currentTab === 'grafik' }" @click="currentTab = 'grafik'">Grafik Tren</button>
            <button class="tab-btn" :class="{ 'active': currentTab === 'produk' }" @click="currentTab = 'produk'">Produk Terlaris</button>
        </div>

        <div x-show="currentTab === 'ringkasan' || currentTab === 'grafik'">
            <!-- Stats Cards (Hanya muncul di Ringkasan) -->
            <div class="row g-4 mb-4" x-show="currentTab === 'ringkasan'">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-purple">
                            <i class="bi bi-cart3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">Total transaksi</div>
                            <div class="fs-4 fw-bold" style="color: #6b21a8;">{{ number_format($totalPesanan, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">Total pendapatan</div>
                            <div class="fs-4 fw-bold" style="color: #1d4ed8;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-red">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">Total pengeluaran</div>
                            <div class="fs-4 fw-bold" style="color: #b91c1c;">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-green">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold mb-1">Laba bersih</div>
                            <div class="fs-4 fw-bold" style="color: #15803d;">Rp {{ number_format($totalLaba, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="chart-container mb-4">
                <div class="chart-title">{{ $chartTitle }}</div>
                <div style="height: 300px;">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <!-- Donut Chart -->
                <div class="col-md-5">
                    <div class="chart-container h-100">
                        <div class="chart-title">Metode Pembayaran</div>
                        <div class="d-flex align-items-center h-100 pb-4">
                            <div style="width: 200px; height: 200px; margin: 0 auto;">
                                <canvas id="donutChart"></canvas>
                            </div>
                            <div class="pe-3">
                                <div class="d-flex align-items-center justify-content-between mb-3 gap-4">
                                    <div><span class="d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: #10b981;"></span><span class="small fw-bold text-muted">Tunai</span></div>
                                    <div class="small fw-bold">{{ $paymentMethodsCount['cash'] }} ({{ $paymentMethodsPercents['cash'] }}%)</div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-3 gap-4">
                                    <div><span class="d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: #f59e0b;"></span><span class="small fw-bold text-muted">QRIS</span></div>
                                    <div class="small fw-bold">{{ $paymentMethodsCount['qris'] }} ({{ $paymentMethodsPercents['qris'] }}%)</div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-3 gap-4">
                                    <div><span class="d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: #3b82f6;"></span><span class="small fw-bold text-muted">Transfer/Debit</span></div>
                                    <div class="small fw-bold">{{ $paymentMethodsCount['transfer'] }} ({{ $paymentMethodsPercents['transfer'] }}%)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="col-md-7">
                    <div class="chart-container h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="chart-title mb-0">Pendapatan vs Pengeluaran</div>
                            <div class="d-flex gap-3">
                                <div class="small"><span class="d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: #10b981;"></span><span class="text-muted fw-bold">Pendapatan</span></div>
                                <div class="small"><span class="d-inline-block rounded-circle me-2" style="width: 10px; height: 10px; background-color: #8b211e;"></span><span class="text-muted fw-bold">Pengeluaran</span></div>
                            </div>
                        </div>
                        <div style="height: 220px;">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="currentTab === 'produk'" style="display: none;">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-5 text-center">
                    <i class="bi bi-cone-striped text-warning mb-3" style="font-size: 3rem;"></i>
                    <h5>Fitur Dalam Pengembangan</h5>
                    <p class="text-muted mb-0">Daftar produk terlaris sedang dipersiapkan untuk pembaruan berikutnya.</p>
                </div>
            </div>
        </div>
        
    </div>
</div>

@push('scripts')
<script>
    // Live Clock
    function updateHeaderTime() {
        const now = new Date();
        document.getElementById('currentTimeHeader').innerText = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
    }
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);

    // Chart.js Implementations
    document.addEventListener('DOMContentLoaded', function() {
        const chartLabels = {!! json_encode($chartLabels) !!};
        const chartPendapatan = {!! json_encode($chartPendapatan) !!};
        const chartPengeluaran = {!! json_encode($chartPengeluaran) !!};
        const paymentMethodsPercents = {!! json_encode(array_values($paymentMethodsPercents)) !!};

        // Shared Configuration
        Chart.defaults.font.family = "'Inter', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
        Chart.defaults.color = '#9ca3af';

        // 1. Line Chart (Pendapatan Harian)
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        
        // Create Gradient
        let gradient = lineCtx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(139, 33, 30, 0.2)');
        gradient.addColorStop(1, 'rgba(139, 33, 30, 0)');

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Pendapatan',
                    data: chartPendapatan,
                    borderColor: '#8b211e',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#8b211e',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 12,
                        titleFont: { size: 13 },
                        bodyFont: { size: 14, weight: 'bold' },
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: function(value) { return value.toLocaleString('id-ID'); } },
                        border: { display: false, dash: [5, 5] },
                        grid: { color: '#f3f4f6', drawBorder: false }
                    },
                    x: {
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });

        // 2. Donut Chart (Metode Pembayaran)
        const donutCtx = document.getElementById('donutChart').getContext('2d');
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Tunai', 'QRIS', 'Debit/Transfer'],
                datasets: [{
                    data: paymentMethodsPercents,
                    backgroundColor: ['#10b981', '#f59e0b', '#3b82f6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });

        // 3. Bar Chart (Pendapatan vs Pengeluaran)
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: chartPendapatan,
                        backgroundColor: '#10b981',
                        borderWidth: 0,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Pengeluaran',
                        data: chartPengeluaran,
                        backgroundColor: '#8b211e',
                        borderWidth: 0,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: function(value) { return value.toLocaleString('id-ID'); } },
                        border: { display: false, dash: [5, 5] },
                        grid: { color: '#f3f4f6', drawBorder: false }
                    },
                    x: {
                        grid: { display: false, drawBorder: false }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
