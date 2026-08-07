@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg mr-1"></i>Tambah User</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead class="table-light">
                    <tr><th>Nama</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th class="text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-{{ $user->role->value === 'admin' ? 'dark' : ($user->role->value === 'kasir' ? 'primary' : ($user->role->value === 'dapur' ? 'warning' : 'success')) }}">{{ $user->role->name }}</span></td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td><span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
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
