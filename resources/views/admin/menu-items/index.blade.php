@extends('layouts.app')
@section('title', 'Menu Items')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.menu-items.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg mr-1"></i>Tambah Menu</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach($menuItems as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->name }}</td>
                        <td>{{ $item->category?->name ?? '-' }}</td>
                        <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>{{ $item->stock }}</td>
                        <td><span class="badge bg-{{ $item->is_active ? 'success' : 'danger' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('admin.menu-items.edit', $item) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.menu-items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
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
