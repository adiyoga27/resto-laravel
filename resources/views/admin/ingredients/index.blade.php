@extends('layouts.app')
@section('title', 'Bahan Baku')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-boxes mr-2"></i>Daftar Bahan Baku</h5>
    <a href="{{ route('admin.ingredients.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i>Tambah</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0 datatable">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Satuan</th>
                    <th class="text-right">Stok</th>
                    <th class="text-right">Min Stok</th>
                    <th class="text-right">Harga Beli</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ingredients as $item)
                <tr>
                    <td class="font-weight-bold">{{ $item->name }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-right {{ $item->isLowStock() ? 'text-danger font-weight-bold' : '' }}">
                        {{ number_format($item->current_stock, 2) }}
                        @if($item->isLowStock())
                            <i class="fas fa-exclamation-triangle text-danger ml-1" title="Stok Rendah!"></i>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->min_stock, 2) }}</td>
                    <td class="text-right">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">
                            {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.ingredients.edit', $item) }}" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.ingredients.destroy', $item) }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus bahan baku ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $ingredients->links() }}</div>
@endsection
