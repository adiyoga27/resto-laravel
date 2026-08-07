@extends('layouts.app')
@section('title', 'Tambah User')

@section('content')
<div class="card border-0 shadow-sm mx-auto" style="max-width:600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
                @error('password')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone (opsional)</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-check-lg mr-1"></i>Simpan</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
