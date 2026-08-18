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
            <button class="btn btn-primary btn-sm mr-2"><i class="fas fa-filter mr-1"></i>Filter</button>
            <a href="{{ route('reports.sales.export', request()->only(['start_date', 'end_date'])) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel mr-1"></i>Export Excel</a>
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
            <thead><tr><th>Order #</th><th>Tanggal</th><th>Tipe</th><th>Channel</th><th>Status</th><th class="text-right">Total</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td class="font-weight-bold">{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->order_type->label() }}</td>
                    <td><span class="badge badge-dark">{{ $order->channel->value }}</span></td>
                    <td><span class="badge badge-{{ $order->order_status->value === 'selesai' ? 'success' : ($order->order_status->value === 'dibatalkan' ? 'danger' : 'warning') }}">{{ $order->order_status->label() }}</span></td>
                    <td class="text-right font-weight-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('reports.sales.show', $order) }}" class="btn btn-xs btn-info" title="Detail"><i class="fas fa-eye"></i></a>
                        <button class="btn btn-xs btn-warning btn-edit" data-id="{{ $order->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-xs btn-danger btn-delete" data-id="{{ $order->id }}" data-number="{{ $order->order_number }}" title="Hapus"><i class="fas fa-trash"></i></button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>

@endsection

@if(auth()->user()->isAdmin())
@push('modals')
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="order_status" class="form-control" id="editStatus">
                            <option value="baru">Baru</option>
                            <option value="diproses">Diproses</option>
                            <option value="siap">Siap</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Pelanggan</label>
                        <input type="text" name="customer_name" class="form-control" id="editCustomerName">
                    </div>
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="customer_phone" class="form-control" id="editCustomerPhone">
                    </div>
                    <div class="form-group">
                        <label>Diskon</label>
                        <input type="number" name="discount" class="form-control" id="editDiscount" min="0">
                    </div>
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea name="notes" class="form-control" id="editNotes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>
@endpush

@push('scripts')
<script>
$(document).on('click', '.btn-edit', function() {
    var id = $(this).data('id');
    $('#editForm').attr('action', '/reports/sales/' + id);
    $('#editModal').modal('show');
});

$(document).on('click', '.btn-delete', function() {
    var id = $(this).data('id');
    var number = $(this).data('number');
    if (confirm('Yakin hapus order ' + number + '?')) {
        $('#deleteForm').attr('action', '/reports/sales/' + id).submit();
    }
});
</script>
@endpush
@endif
