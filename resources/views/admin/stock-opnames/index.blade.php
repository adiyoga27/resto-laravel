@extends('layouts.app')
@section('title', 'Stok Opname')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-clipboard-check mr-2"></i>Stok Opname</h5>
    <a href="{{ route('admin.stock-opnames.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i>Opname Baru</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0 datatable">
            <thead>
                <tr><th>#</th><th>Tanggal</th><th>Catatan</th><th>Status</th><th>User</th><th class="text-center">Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($opnames as $opname)
                <tr>
                    <td>{{ $opname->id }}</td>
                    <td>{{ $opname->date->format('d/m/Y') }}</td>
                    <td>{{ $opname->notes ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $opname->isDraft() ? 'warning' : 'success' }}">
                            {{ $opname->status->label() }}
                        </span>
                    </td>
                    <td>{{ $opname->user?->name }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.stock-opnames.show', $opname) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                        @if($opname->isDraft())
                        <form action="{{ route('admin.stock-opnames.destroy', $opname) }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus opname ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $opnames->links() }}</div>
@endsection
