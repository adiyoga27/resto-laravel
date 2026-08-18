@extends('layouts.app')
@section('title', 'Tambah Mutasi Stok')

@push('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.stock-logs.index') }}">Mutasi Stok</a></li>
<li class="breadcrumb-item active">{{ $type === 'in' ? 'Barang Masuk' : ($type === 'out' ? 'Barang Keluar' : 'Penyesuaian') }}</li>
@endpush

@section('content')
<div class="card border-0 shadow-sm mx-auto" style="max-width:600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.stock-logs.store') }}">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <h5 class="mb-4">
                {{ $type === 'in' ? 'Barang Masuk' : ($type === 'out' ? 'Barang Keluar' : 'Penyesuaian Stok') }}
            </h5>
            <div class="form-group">
                <label>Bahan Baku</label>
                <select name="ingredient_id" class="form-control" required>
                    <option value="">Pilih Bahan</option>
                    @foreach($ingredients as $ing)
                        <option value="{{ $ing->id }}" {{ old('ingredient_id') == $ing->id ? 'selected' : '' }}>
                            {{ $ing->name }} (Stok: {{ number_format($ing->current_stock, 2) }} {{ $ing->unit }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah</label>
                <input type="number" name="quantity" class="form-control" required step="0.01" min="0.01" value="{{ old('quantity') }}" placeholder="0.00">
                @if($type === 'out')
                    <small class="text-danger">Stok akan berkurang.</small>
                @elseif($type === 'in')
                    <small class="text-success">Stok akan bertambah.</small>
                @endif
            </div>
            <div class="form-group">
                <label>Referensi (opsional)</label>
                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="No. nota / faktur">
            </div>
            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-save mr-1"></i>Simpan</button>
                <a href="{{ route('admin.stock-logs.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
