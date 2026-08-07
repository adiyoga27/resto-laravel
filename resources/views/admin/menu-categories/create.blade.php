@extends('layouts.app')
@section('title', 'Tambah Kategori')

@section('content')
<h3 class="fw-bold mb-4"><i class="bi bi-folder-plus mr-2"></i>Tambah Kategori Menu</h3>
<div class="card border-0 shadow-sm mx-auto" style="max-width:600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.menu-categories.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama Kategori</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Urutan</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-control">
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-check-lg mr-1"></i>Simpan</button>
                <a href="{{ route('admin.menu-categories.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
