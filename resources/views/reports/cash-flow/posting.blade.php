@extends('layouts.app')
@section('title', 'Posting Transaksi ke Arus Kas')

@push('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reports.cash-flow') }}">Arus Kas</a></li>
<li class="breadcrumb-item active">Posting Transaksi</li>
@endpush

@push('styles')
<style>
.total-box { font-size:1.2rem; font-weight:700; }
</style>
@endpush

@section('content')

<div class="card mb-3">
    <div class="card-body py-2 d-flex align-items-center flex-wrap">
        <form method="GET" class="form-inline mr-3">
            <label class="mr-2 small text-muted">Dari</label>
            <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <label class="mr-2 small text-muted">Sampai</label>
            <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
            <button class="btn btn-primary btn-sm mr-2"><i class="fas fa-filter mr-1"></i>Filter</button>
        </form>
        <a href="{{ route('reports.cash-flow') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
    </div>
</div>

@if($orders->isNotEmpty())
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="POST" action="{{ route('reports.cash-flow.post-transaction') }}" id="bulkPostForm">
            @csrf
            <div class="d-flex align-items-center flex-wrap">
                <label class="mr-2 font-weight-bold mb-0">Tanggal Posting:</label>
                <input type="date" name="posting_date" class="form-control form-control-sm mr-3" required value="{{ now()->format('Y-m-d') }}">
                <span class="mr-3 text-muted small">
                    Terpilih: <strong id="selectedCount">0</strong> transaksi,
                    Total: <strong id="selectedTotal" class="text-success">Rp 0</strong>
                </span>
                <button type="submit" class="btn btn-success btn-sm" id="btnBulkPost" disabled onclick="return confirm('Posting transaksi terpilih ke Arus Kas?')">
                    <i class="fas fa-paper-plane mr-1"></i>Posting Sekarang
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list mr-2"></i>Transaksi Selesai — Belum Diposting
            @if($orders->isNotEmpty())
                <button type="button" class="btn btn-xs btn-outline-secondary ml-2" onclick="toggleAll(this)">Pilih Semua</button>
            @endif
        </h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead>
                <tr>
                    <th width="40" class="text-center">#</th>
                    <th>Order #</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="order-checkbox" value="{{ $order->id }}" data-total="{{ $order->total }}">
                    </td>
                    <td class="font-weight-bold">{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->customer_name ?? '-' }}</td>
                    <td class="text-right font-weight-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Tidak ada transaksi yang perlu diposting.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>

@endsection

@push('scripts')
<script>
function updateSummary() {
    var checked = document.querySelectorAll('.order-checkbox:checked');
    var count = checked.length;
    var total = 0;
    checked.forEach(function(cb) {
        total += parseFloat(cb.dataset.total);
    });
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('selectedTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('btnBulkPost').disabled = count === 0;
}

document.querySelectorAll('.order-checkbox').forEach(function(cb) {
    cb.addEventListener('change', updateSummary);
});

function toggleAll(btn) {
    var allSelected = document.querySelectorAll('.order-checkbox:checked').length === document.querySelectorAll('.order-checkbox').length;
    document.querySelectorAll('.order-checkbox').forEach(function(cb) {
        cb.checked = !allSelected;
    });
    btn.textContent = allSelected ? 'Pilih Semua' : 'Batal Pilih';
    updateSummary();
}

document.getElementById('bulkPostForm').addEventListener('submit', function(e) {
    var checked = document.querySelectorAll('.order-checkbox:checked');
    checked.forEach(function(cb) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'order_ids[]';
        input.value = cb.value;
        this.appendChild(input);
    }, this);
});
</script>
@endpush
