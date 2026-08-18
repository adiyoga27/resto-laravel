@extends('layouts.app')
@section('title', 'Dashboard Admin')

@push('styles')
<style>
.period-tab { cursor:pointer; padding:6px 18px; border-radius:6px; font-size:.85rem; font-weight:500; transition:all .2s; }
.period-tab.active { background:#6366f1; color:#fff; }
.period-tab:not(.active) { color:#64748b; }
.period-tab:not(.active):hover { background:#f1f5f9; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
var salesChart;
var chartData = {
    daily: {
        labels: @json($dailyData['labels']),
        revenue: @json($dailyData['revenue']),
        orders: @json($dailyData['orders']),
        title: 'Grafik Omzet Harian (30 Hari Terakhir)',
        xLabel: 'Tanggal'
    },
    weekly: {
        labels: @json($weeklyData['labels']),
        revenue: @json($weeklyData['revenue']),
        orders: @json($weeklyData['orders']),
        title: 'Grafik Omzet Mingguan (12 Minggu Terakhir)',
        xLabel: 'Minggu'
    },
    monthly: {
        labels: @json($monthlyData['labels']),
        revenue: @json($monthlyData['revenue']),
        orders: @json($monthlyData['orders']),
        title: 'Grafik Omzet Bulanan ({{ now()->year }})',
        xLabel: 'Bulan'
    }
};

function renderChart(period) {
    var d = chartData[period];
    var ctx = document.getElementById('salesChart').getContext('2d');

    if (salesChart) { salesChart.destroy(); }

    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: [{
                label: 'Omzet (Rp)',
                data: d.revenue,
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderColor: 'rgba(99,102,241,1)',
                borderWidth: 1,
                borderRadius: 6,
                order: 1,
            }, {
                label: 'Jumlah Order',
                data: d.orders,
                type: 'line',
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#f59e0b',
                pointRadius: 3,
                pointHoverRadius: 6,
                tension: 0.3,
                order: 0,
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.dataset.label === 'Omzet (Rp)') {
                                return 'Omzet: Rp ' + ctx.raw.toLocaleString('id-ID');
                            }
                            return 'Order: ' + ctx.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) { return 'Rp ' + (v/1000000).toFixed(0) + 'jt'; }
                    },
                    grid: { color: '#f1f5f9' }
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { drawOnChartArea: false }
                },
                x: {
                    grid: { display: false },
                    ticks: period === 'daily' ? { maxTicksLimit: 15, autoSkip: true } : {}
                }
            }
        }
    });

    document.getElementById('chartTitle').textContent = d.title;
}

$(function(){
    renderChart('daily');

    $('.period-tab').on('click', function(){
        $('.period-tab').removeClass('active');
        $(this).addClass('active');
        renderChart($(this).data('period'));
    });
});
</script>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="icon-box mr-3" style="background:rgba(79,70,229,.1); color:#4f46e5;">
                    <i class="fas fa-receipt fa-lg"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Total Orders</p>
                    <h3 class="mb-0 font-weight-bold">{{ $totalOrders }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="icon-box mr-3" style="background:rgba(16,185,129,.1); color:#10b981;">
                    <i class="fas fa-coins fa-lg"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Revenue</p>
                    <h3 class="mb-0 font-weight-bold text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="icon-box mr-3" style="background:rgba(245,158,11,.1); color:#f59e0b;">
                    <i class="fas fa-clock fa-lg"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Active</p>
                    <h3 class="mb-0 font-weight-bold">{{ $activeOrders }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="icon-box mr-3" style="background:rgba(6,182,212,.1); color:#06b6d4;">
                    <i class="fas fa-users fa-lg"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Customers</p>
                    <h3 class="mb-0 font-weight-bold">{{ $totalCustomers }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="card border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="font-weight-bold mb-0" style="font-size:1rem;color:#1e293b;" id="chartTitle">
                    <i class="fas fa-chart-bar mr-2" style="color:#6366f1;"></i>Grafik Omzet Harian
                </h5>
                <div>
                    <span class="period-tab active" data-period="daily">Harian</span>
                    <span class="period-tab" data-period="weekly">Mingguan</span>
                    <span class="period-tab" data-period="monthly">Bulanan</span>
                    <a href="{{ route('admin.dashboard.export') }}" class="btn btn-sm btn-success ml-3"><i class="fas fa-file-excel mr-1"></i>Export Excel</a>
                </div>
            </div>
            <div class="card-body" style="height:380px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection
