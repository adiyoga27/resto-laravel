@extends('layouts.app')
@section('title', 'Edit Meja')

@section('content')
<h3 class="fw-bold mb-4"><i class="bi bi-pencil-square mr-2"></i>Edit Meja: {{ $table->table_number }}</h3>
<div class="card border-0 shadow-sm mx-auto" style="max-width:500px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.tables.update', $table) }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Nomor Meja</label><input type="text" name="table_number" value="{{ old('table_number', $table->table_number) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Kapasitas (orang)</label><input type="number" name="capacity" value="{{ old('capacity', $table->capacity) }}" min="1" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select" required>@foreach($statuses as $status)<option value="{{ $status->value }}" {{ old('status', $table->status->value) === $status->value ? 'selected' : '' }}>{{ $status->name }}</option>@endforeach</select></div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-check-lg mr-1"></i>Update</button>
                <a href="{{ route('admin.tables.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
