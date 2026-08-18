@extends('layouts.app')
@section('title', 'Mutasi Stok')

@push('breadcrumb')
<li class="breadcrumb-item active">Mutasi Stok</li>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-exchange-alt mr-2"></i>Mutasi Stok Bahan Baku</h5>
    <div>
        <a href="{{ route('admin.stock-logs.create', ['type' => 'in']) }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i>Barang Masuk</a>
        <a href="{{ route('admin.stock-logs.create', ['type' => 'out']) }}" class="btn btn-danger btn-sm"><i class="fas fa-minus mr-1"></i>Barang Keluar</a>
        <a href="{{ route('admin.stock-logs.create-production') }}" class="btn btn-warning btn-sm"><i class="fas fa-industry mr-1"></i>Produksi</a>
    </div>
</div>

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

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0 datatable">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Bahan</th>
                    <th>Menu</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Stok Sebelum</th>
                    <th class="text-right">Stok Sesudah</th>
                    <th>Ref</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td><span class="badge badge-{{ $log->type->value === 'in' || $log->type->value === 'production' ? 'success' : ($log->type->value === 'out' ? 'danger' : 'info') }}">{{ $log->type->label() }}</span></td>
                    <td>{{ $log->ingredient?->name ?? '-' }}</td>
                    <td>{{ $log->menuItem?->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($log->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($log->stock_before, 2) }}</td>
                    <td class="text-right">{{ number_format($log->stock_after, 2) }}</td>
                    <td>{{ $log->reference ?? '-' }}</td>
                    <td>{{ $log->user?->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
