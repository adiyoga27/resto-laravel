@extends('layouts.app')
@section('title', 'Detail Stok Opname #' . $stockOpname->id)

@push('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.stock-opnames.index') }}">Stok Opname</a></li>
<li class="breadcrumb-item active">#{{ $stockOpname->id }}</li>
@endpush

@section('content')

<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('admin.stock-opnames.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
        @if($stockOpname->isDraft())
            <form action="{{ route('admin.stock-opnames.post', $stockOpname) }}" method="POST" class="d-inline" onsubmit="return confirm('Posting opname? Stok bahan akan disesuaikan sesuai stok fisik.')">
                @csrf
                <button type="submit" class="btn btn-success btn-sm ml-2"><i class="fas fa-check mr-1"></i>Posting & Sesuaikan Stok</button>
            </form>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-3">
            <div class="card-body">
                <dl class="mb-0">
                    <dt>Tanggal</dt><dd>{{ $stockOpname->date->format('d/m/Y') }}</dd>
                    <dt>Status</dt><dd><span class="badge badge-{{ $stockOpname->isDraft() ? 'warning' : 'success' }}">{{ $stockOpname->status->label() }}</span></dd>
                    <dt>Dibuat oleh</dt><dd>{{ $stockOpname->user?->name }}</dd>
                    @if($stockOpname->notes)<dt>Catatan</dt><dd>{{ $stockOpname->notes }}</dd>@endif
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Bahan</th><th>Satuan</th><th class="text-right">Stok Sistem</th><th class="text-right">Stok Fisik</th><th class="text-right">Selisih</th></tr></thead>
                    <tbody>
                        @foreach($stockOpname->items as $item)
                        <tr>
                            <td>{{ $item->ingredient->name }}</td>
                            <td>{{ $item->ingredient->unit }}</td>
                            <td class="text-right">{{ number_format($item->system_stock, 2) }}</td>
                            <td class="text-right font-weight-bold">{{ number_format($item->actual_stock, 2) }}</td>
                            <td class="text-right {{ $item->difference < 0 ? 'text-danger' : ($item->difference > 0 ? 'text-success' : '') }}">
                                {{ $item->difference > 0 ? '+' : '' }}{{ number_format($item->difference, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
