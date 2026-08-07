@extends('layouts.app')
@section('title', 'Tambah Meja')

@section('content')
<div class="card border-0 shadow-sm mx-auto" style="max-width:500px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.tables.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label">Nomor Meja</label><input type="text" name="table_number" value="{{ old('table_number') }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Kapasitas (orang)</label><input type="number" name="capacity" value="{{ old('capacity', 2) }}" min="1" class="form-control" required></div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-check-lg mr-1"></i>Simpan</button>
                <a href="{{ route('admin.tables.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
