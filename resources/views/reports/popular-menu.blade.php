@extends('layouts.app')
@section('title', 'Menu Terlaris')

@section('content')

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <label class="mr-2 small text-muted">Dari</label>
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <label class="mr-2 small text-muted">Sampai</label>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-primary btn-sm mr-2"><i class="fas fa-filter mr-1"></i>Filter</button>
            <a href="{{ route('reports.popular-menu.export', request()->only(['start_date', 'end_date'])) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel mr-1"></i>Export Excel</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover datatable">
            <thead><tr><th>#</th><th>Nama Menu</th><th>Kategori</th><th>Harga</th><th class="text-right">Total Terjual</th></tr></thead>
            <tbody>
                @foreach($popularItems as $i => $item)
                <tr>
                    <td class="font-weight-bold">{{ $i + 1 }}</td>
                    <td class="font-weight-bold">{{ $item->name }}</td>
                    <td>{{ $item->category?->name ?? '-' }}</td>
                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right font-weight-bold text-primary">{{ $item->total_orders }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $popularItems->links() }}</div>
@endsection
