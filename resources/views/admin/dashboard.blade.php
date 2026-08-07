@extends('layouts.app')
@section('title', 'Dashboard Admin')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
$(function(){
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($revenueData),
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderColor: 'rgba(99,102,241,1)',
                borderWidth: 1,
                borderRadius: 6,
                order: 1,
            }, {
                label: 'Jumlah Order',
                data: @json($orderData),
                type: 'line',
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#f59e0b',
                pointRadius: 4,
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
                            if (ctx.dataset.label === 'Pendapatan (Rp)') {
                                return 'Pendapatan: Rp ' + ctx.raw.toLocaleString('id-ID');
                            }
                            return ctx.dataset.label + ': ' + ctx.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) { return 'Rp ' + (v/1000000).toFixed(1) + 'jt'; }
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
                    grid: { display: false }
                }
            }
        }
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
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="font-weight-bold mb-0" style="font-size:1rem;color:#1e293b;">
                    <i class="fas fa-chart-bar mr-2" style="color:#6366f1;"></i>Grafik Penjualan {{ now()->year }}
                </h5>
            </div>
            <div class="card-body" style="height:360px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection
