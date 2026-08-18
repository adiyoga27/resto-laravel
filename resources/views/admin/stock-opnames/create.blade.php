@extends('layouts.app')
@section('title', 'Buat Stok Opname')

@push('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.stock-opnames.index') }}">Stok Opname</a></li>
<li class="breadcrumb-item active">Buat</li>
@endpush

@section('content')
<div class="card">
    <div class="card-header"><h6 class="mb-0">Input Stok Fisik Bahan Baku</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.stock-opnames.store') }}">
            @csrf
            <div class="form-row mb-3">
                <div class="col-md-3">
                    <label>Tanggal Opname</label>
                    <input type="date" name="date" class="form-control" required value="{{ old('date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label>Catatan</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Opsional">
                </div>
            </div>

            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Bahan Baku</th>
                        <th>Satuan</th>
                        <th class="text-right">Stok Sistem</th>
                        <th class="text-right">Stok Fisik</th>
                        <th class="text-right">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingredients as $ing)
                    <tr>
                        <td>{{ $ing->name }}</td>
                        <td>{{ $ing->unit }}</td>
                        <td class="text-right">{{ number_format($ing->current_stock, 2) }}</td>
                        <td>
                            <input type="number" name="actual_stock[{{ $ing->id }}]" class="form-control form-control-sm text-right opname-input"
                                step="0.01" min="0"
                                data-system="{{ $ing->current_stock }}"
                                data-row="{{ $ing->id }}"
                                value="{{ old('actual_stock.'.$ing->id, $ing->current_stock) }}">
                        </td>
                        <td class="text-right">
                            <span id="diff_{{ $ing->id }}" class="font-weight-bold">0.00</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex mt-3">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-save mr-1"></i>Simpan Opname</button>
                <a href="{{ route('admin.stock-opnames.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.opname-input').forEach(function(input) {
    input.addEventListener('input', function() {
        var system = parseFloat(this.dataset.system);
        var actual = parseFloat(this.value) || 0;
        var diff = actual - system;
        var el = document.getElementById('diff_' + this.dataset.row);
        el.textContent = diff.toFixed(2);
        el.className = 'font-weight-bold ' + (diff < 0 ? 'text-danger' : (diff > 0 ? 'text-success' : ''));
    });
});
</script>
@endpush
