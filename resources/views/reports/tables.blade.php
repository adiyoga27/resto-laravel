@extends('layouts.app')
@section('title', 'Laporan Meja')

@section('content')

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <label class="mr-2 small text-muted">Dari</label>
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <label class="mr-2 small text-muted">Sampai</label>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-primary btn-sm mr-2"><i class="fas fa-filter mr-1"></i>Filter</button>
            <a href="{{ route('reports.tables.export', request()->only(['start_date', 'end_date'])) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel mr-1"></i>Export Excel</a>
        </form>
    </div>
</div>

<div class="row">
    @foreach($tableUsage as $table)
    <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
        <div class="card stat-card text-center border-0 h-100">
            <div class="card-body p-4">
                <div class="mb-3" style="font-size:2.5rem;">🪑</div>
                <h5 class="font-weight-bold mb-1">Meja {{ $table->table_number }}</h5>
                <p class="text-muted small mb-3">Kapasitas: {{ $table->capacity }} orang</p>
                <div style="background:rgba(79,70,229,.08);border-radius:12px;padding:.75rem;">
                    <h3 class="mb-0 font-weight-bold" style="color:#4f46e5;">{{ $table->orders_count }}</h3>
                    <small class="text-muted">orders</small>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
