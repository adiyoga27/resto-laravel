@extends('layouts.app')
@section('title', 'Kategori Menu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.menu-categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg mr-1"></i>Tambah Kategori</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Deskripsi</th><th>Urutan</th><th>Status</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                    <tr>
                        <td class="fw-semibold">{{ $cat->name }}</td>
                        <td>{{ Str::limit($cat->description, 60) }}</td>
                        <td>{{ $cat->sort_order }}</td>
                        <td><span class="badge bg-{{ $cat->is_active ? 'success' : 'danger' }}">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('admin.menu-categories.edit', $cat) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.menu-categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
