@extends('layouts.app')
@section('title', 'Edit Kategori')

@section('content')
<h3 class="fw-bold mb-4"><i class="bi bi-pencil-square mr-2"></i>Edit Kategori: {{ $menuCategory->name }}</h3>
<div class="card border-0 shadow-sm mx-auto" style="max-width:600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.menu-categories.update', $menuCategory) }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Nama Kategori</label><input type="text" name="name" value="{{ old('name', $menuCategory->name) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="description" rows="3" class="form-control">{{ old('description', $menuCategory->description) }}</textarea></div>
            <div class="mb-3"><label class="form-label">Urutan</label><input type="number" name="sort_order" value="{{ old('sort_order', $menuCategory->sort_order) }}" class="form-control"></div>
            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $menuCategory->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-check-lg mr-1"></i>Update</button>
                <a href="{{ route('admin.menu-categories.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
