@extends('layouts.app')
@section('title', 'Laporan Arus Kas')

@section('content')

<div class="card mb-3">
    <div class="card-body py-2 d-flex align-items-center">
        <form method="GET" class="form-inline">
            <label class="mr-2 small text-muted">Dari</label>
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <label class="mr-2 small text-muted">Sampai</label>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-primary btn-sm mr-3"><i class="fas fa-filter mr-1"></i>Filter</button>
        </form>
        <a href="{{ route('reports.cash-flow.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i>Tambah Entri</a>
        <a href="{{ route('reports.cash-flow.posting') }}" class="btn btn-info btn-sm ml-2"><i class="fas fa-paper-plane mr-1"></i>Posting Transaksi</a>
        <a href="{{ route('reports.cash-flow.export', request()->only(['start_date', 'end_date'])) }}" class="btn btn-outline-success btn-sm ml-2"><i class="fas fa-file-excel mr-1"></i>Export Excel</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="icon-box mr-3" style="background:rgba(16,185,129,.1);color:#10b981;"><i class="fas fa-arrow-down fa-lg"></i></div>
                <div><p class="text-muted small mb-0" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Total Debit (Masuk)</p><h3 class="mb-0 font-weight-bold text-success">Rp {{ number_format($totalDebit, 0, ',', '.') }}</h3></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="icon-box mr-3" style="background:rgba(239,68,68,.1);color:#ef4444;"><i class="fas fa-arrow-up fa-lg"></i></div>
                <div><p class="text-muted small mb-0" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Total Kredit (Keluar)</p><h3 class="mb-0 font-weight-bold text-danger">Rp {{ number_format($totalKredit, 0, ',', '.') }}</h3></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4 mb-3">
        <div class="card stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center p-4">
                <div class="icon-box mr-3" style="background:rgba(79,70,229,.1);color:#4f46e5;"><i class="fas fa-balance-scale fa-lg"></i></div>
                <div><p class="text-muted small mb-0" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Saldo</p><h3 class="mb-0 font-weight-bold {{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($saldo, 0, ',', '.') }}</h3></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover datatable">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Ref</th>
                    <th>Tipe</th>
                    <th class="text-right">Jumlah</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                <tr>
                    <td>{{ $entry->date->format('d/m/Y') }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ $entry->reference ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $entry->type->value === 'debit' ? 'success' : 'danger' }}">
                            {{ $entry->type->label() }}
                        </span>
                    </td>
                    <td class="text-right font-weight-bold">
                        {{ $entry->type->value === 'debit' ? '+' : '-' }} Rp {{ number_format($entry->amount, 0, ',', '.') }}
                    </td>
                    <td>
                        @if($entry->is_posted)
                            <span class="badge badge-success">Posted</span>
                        @else
                            <span class="badge badge-warning">Draft</span>
                        @endif
                    </td>
                    <td>{{ $entry->creator?->name ?? '-' }}</td>
                    <td class="text-center">
                        @if($entry->is_posted)
                            <form action="{{ route('reports.cash-flow.unpost', $entry) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-warning" title="Batal Posting" onclick="return confirm('Batal posting entri ini?')"><i class="fas fa-undo"></i></button>
                            </form>
                        @else
                            <form action="{{ route('reports.cash-flow.post', $entry) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-success" title="Posting" onclick="return confirm('Posting entri ini?')"><i class="fas fa-check"></i></button>
                            </form>
                            <a href="{{ route('reports.cash-flow.edit', $entry) }}" class="btn btn-xs btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('reports.cash-flow.destroy', $entry) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus entri ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $entries->links() }}</div>

@endsection
