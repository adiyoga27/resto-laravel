@extends('layouts.app')
@section('title', 'Edit Menu')

@section('content')
<h3 class="fw-bold mb-4"><i class="bi bi-pencil-square mr-2"></i>Edit Menu: {{ $menuItem->name }}</h3>
<div class="card border-0 shadow-sm mx-auto" style="max-width:600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.menu-items.update', $menuItem) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Kategori</label><select name="menu_category_id" class="form-select" required>@foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('menu_category_id', $menuItem->menu_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Nama Menu</label><input type="text" name="name" value="{{ old('name', $menuItem->name) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="description" rows="3" class="form-control">{{ old('description', $menuItem->description) }}</textarea></div>
            <div class="mb-3"><label class="form-label">Harga (Rp)</label><input type="number" name="price" value="{{ old('price', $menuItem->price) }}" step="0.01" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Stok</label><input type="number" name="stock" value="{{ old('stock', $menuItem->stock) }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Urutan</label><input type="number" name="sort_order" value="{{ old('sort_order', $menuItem->sort_order) }}" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Foto</label><input type="file" name="image" class="form-control"></div>
            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $menuItem->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-check-lg mr-1"></i>Update</button>
                <a href="{{ route('admin.menu-items.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
