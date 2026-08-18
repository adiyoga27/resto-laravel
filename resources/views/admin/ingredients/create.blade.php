@extends('layouts.app')
@section('title', 'Tambah Bahan Baku')

@push('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.ingredients.index') }}">Bahan Baku</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endpush

@section('content')
<div class="card border-0 shadow-sm mx-auto" style="max-width:600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.ingredients.store') }}">
            @csrf
            <div class="form-group">
                <label>Nama Bahan</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="Contoh: Tepung Terigu">
            </div>
            <div class="form-group">
                <label>Satuan</label>
                <input type="text" name="unit" class="form-control" required value="{{ old('unit') }}" placeholder="kg, gr, liter, ml, pcs">
            </div>
            <div class="form-group">
                <label>Stok Saat Ini</label>
                <input type="number" name="current_stock" class="form-control" step="0.01" min="0" value="{{ old('current_stock', 0) }}">
            </div>
            <div class="form-group">
                <label>Stok Minimum</label>
                <input type="number" name="min_stock" class="form-control" step="0.01" min="0" value="{{ old('min_stock', 0) }}">
            </div>
            <div class="form-group">
                <label>Harga Beli (Rp)</label>
                <input type="number" name="cost_price" class="form-control" step="0.01" min="0" value="{{ old('cost_price', 0) }}">
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-save mr-1"></i>Simpan</button>
                <a href="{{ route('admin.ingredients.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
