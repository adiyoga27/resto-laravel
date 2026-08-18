@extends('layouts.app')
@section('title', 'Edit Bahan Baku')

@push('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.ingredients.index') }}">Bahan Baku</a></li>
<li class="breadcrumb-item active">Edit</li>
@endpush

@section('content')
<div class="card border-0 shadow-sm mx-auto" style="max-width:600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.ingredients.update', $ingredient) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nama Bahan</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $ingredient->name) }}">
            </div>
            <div class="form-group">
                <label>Satuan</label>
                <input type="text" name="unit" class="form-control" required value="{{ old('unit', $ingredient->unit) }}">
            </div>
            <div class="form-group">
                <label>Stok Saat Ini</label>
                <input type="number" name="current_stock" class="form-control" step="0.01" min="0" value="{{ old('current_stock', $ingredient->current_stock) }}">
            </div>
            <div class="form-group">
                <label>Stok Minimum</label>
                <input type="number" name="min_stock" class="form-control" step="0.01" min="0" value="{{ old('min_stock', $ingredient->min_stock) }}">
            </div>
            <div class="form-group">
                <label>Harga Beli (Rp)</label>
                <input type="number" name="cost_price" class="form-control" step="0.01" min="0" value="{{ old('cost_price', $ingredient->cost_price) }}">
            </div>
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="isActive" name="is_active" value="1" {{ $ingredient->is_active ? 'checked' : '' }}>
                    <label class="custom-control-label" for="isActive">Aktif</label>
                </div>
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-save mr-1"></i>Simpan</button>
                <a href="{{ route('admin.ingredients.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
