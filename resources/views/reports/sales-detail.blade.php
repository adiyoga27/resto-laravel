@extends('layouts.app')
@section('title', 'Detail Transaksi #' . $order->order_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reports.sales') }}">Laporan Penjualan</a></li>
<li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')

<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('reports.sales', request()->only(['start_date', 'end_date'])) }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-receipt mr-2"></i>Item Pesanan</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Menu</th><th>Kategori</th><th class="text-center">Qty</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($order->orderItems as $item)
                        <tr>
                            <td>{{ $item->menuItem?->name ?? '-' }}</td>
                            <td>{{ $item->menuItem?->category?->name ?? '-' }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th colspan="4" class="text-right">Subtotal</th><th class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</th></tr>
                        <tr><th colspan="4" class="text-right">Diskon</th><th class="text-right text-danger">- Rp {{ number_format($order->discount, 0, ',', '.') }}</th></tr>
                        <tr><th colspan="4" class="text-right">Tax (11%)</th><th class="text-right">Rp {{ number_format($order->tax, 0, ',', '.') }}</th></tr>
                        <tr><th colspan="4" class="text-right">Total</th><th class="text-right font-weight-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</th></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($order->payments->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-credit-card mr-2"></i>Pembayaran</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Metode</th><th>Referensi</th><th class="text-right">Jumlah</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($order->payments as $payment)
                        <tr>
                            <td>{{ $payment->method->value }}</td>
                            <td>{{ $payment->reference ?? '-' }}</td>
                            <td class="text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td>{{ $payment->status->value }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle mr-2"></i>Info Transaksi</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Order #</dt>
                    <dd class="col-sm-7 font-weight-bold">{{ $order->order_number }}</dd>

                    <dt class="col-sm-5">Tanggal</dt>
                    <dd class="col-sm-7">{{ $order->created_at->format('d/m/Y H:i') }}</dd>

                    <dt class="col-sm-5">Status</dt>
                    <dd class="col-sm-7">
                        <span class="badge badge-{{ $order->order_status->value === 'selesai' ? 'success' : ($order->order_status->value === 'dibatalkan' ? 'danger' : 'warning') }}">{{ $order->order_status->label() }}</span>
                    </dd>

                    <dt class="col-sm-5">Tipe</dt>
                    <dd class="col-sm-7">{{ $order->order_type->label() }}</dd>

                    <dt class="col-sm-5">Channel</dt>
                    <dd class="col-sm-7"><span class="badge badge-dark">{{ $order->channel->value }}</span></dd>

                    <dt class="col-sm-5">Meja</dt>
                    <dd class="col-sm-7">{{ $order->restaurantTable?->table_number ?? '-' }}</dd>

                    <dt class="col-sm-5">Kasir</dt>
                    <dd class="col-sm-7">{{ $order->createdBy?->name ?? '-' }}</dd>
                </dl>

                <hr>

                <dl class="row mb-0">
                    <dt class="col-sm-5">Pelanggan</dt>
                    <dd class="col-sm-7">{{ $order->customer_name ?? '-' }}</dd>

                    <dt class="col-sm-5">Telp</dt>
                    <dd class="col-sm-7">{{ $order->customer_phone ?? '-' }}</dd>
                </dl>

                @if($order->notes)
                <hr>
                <strong>Catatan</strong>
                <p class="text-muted mb-0">{{ $order->notes }}</p>
                @endif

                @if(auth()->user()->isAdmin())
                <hr>
                <div class="d-flex justify-content-between">
                    <button class="btn btn-warning btn-sm btn-edit" data-id="{{ $order->id }}"><i class="fas fa-edit mr-1"></i>Edit</button>
                    <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $order->id }}" data-number="{{ $order->order_number }}"><i class="fas fa-trash mr-1"></i>Hapus</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

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
