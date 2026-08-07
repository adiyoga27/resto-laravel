@extends('layouts.app')
@section('title', 'Manajemen Meja')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.tables.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg mr-1"></i>Tambah Meja</a>
</div>
<div class="row g-4">
    @foreach($tables as $table)
    <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-6 mb-2">🪑</div>
                <h5 class="fw-bold">Meja {{ $table->table_number }}</h5>
                <p class="text-muted small mb-2">Kapasitas: {{ $table->capacity }} orang</p>
                <span class="badge bg-{{ $table->status->value === 'kosong' ? 'success' : ($table->status->value === 'terisi' ? 'danger' : 'warning') }} mb-3">{{ $table->status->name }}</span>
                <div class="d-flex justify-content-center">
                    <a href="{{ route('admin.tables.edit', $table) }}" class="btn btn-sm btn-outline-secondary mr-1"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('admin.tables.destroy', $table) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-3">{{ $tables->links() }}</div>
@endsection
