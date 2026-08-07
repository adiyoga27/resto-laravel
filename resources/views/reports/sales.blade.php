@extends('layouts.app')
@section('title', 'Laporan Penjualan')

@section('content')

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <label class="mr-2 small text-muted">Dari</label>
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <label class="mr-2 small text-muted">Sampai</label>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-primary btn-sm"><i class="fas fa-filter mr-1"></i>Filter</button>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="icon-box mr-3" style="background:rgba(79,70,229,.1);color:#4f46e5;"><i class="fas fa-receipt fa-lg"></i></div>
                <div><p class="text-muted small mb-0" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Total Orders</p><h3 class="mb-0 font-weight-bold">{{ $totalOrders }}</h3></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="icon-box mr-3" style="background:rgba(16,185,129,.1);color:#10b981;"><i class="fas fa-coins fa-lg"></i></div>
                <div><p class="text-muted small mb-0" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Revenue</p><h3 class="mb-0 font-weight-bold text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover datatable">
            <thead><tr><th>Order #</th><th>Tanggal</th><th>Tipe</th><th>Channel</th><th>Status</th><th class="text-right">Total</th></tr></thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td class="font-weight-bold">{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->order_type->label() }}</td>
                    <td><span class="badge badge-dark">{{ $order->channel->value }}</span></td>
                    <td><span class="badge badge-{{ $order->order_status->value === 'selesai' ? 'success' : ($order->order_status->value === 'dibatalkan' ? 'danger' : 'warning') }}">{{ $order->order_status->label() }}</span></td>
                    <td class="text-right font-weight-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
