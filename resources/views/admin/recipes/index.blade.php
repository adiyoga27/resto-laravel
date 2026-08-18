@extends('layouts.app')
@section('title', 'Resep Menu')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-blender mr-2"></i>Resep Menu (BOM)</h5>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="form-inline">
            <label class="mr-2 small text-muted">Filter Menu</label>
            <select name="menu_item_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">-- Semua Menu --</option>
                @foreach($menuItems as $menu)
                    <option value="{{ $menu->id }}" {{ $menuItemId == $menu->id ? 'selected' : '' }}>
                        {{ $menu->name }} ({{ $menu->category?->name ?? '-' }})
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        @forelse($recipes as $menuId => $items)
            @php $menu = $items->first()->menuItem; @endphp
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">{{ $menu->name }} <small class="text-muted">({{ $menu->category?->name }})</small></h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead><tr><th>Bahan</th><th>Satuan</th><th class="text-right">Qty per Porsi</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $item->ingredient->name }}</td>
                                <td>{{ $item->ingredient->unit }}</td>
                                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-info btn-edit-recipe"
                                        data-id="{{ $item->id }}"
                                        data-menu="{{ $item->menu_item_id }}"
                                        data-ingredient="{{ $item->ingredient_id }}"
                                        data-quantity="{{ $item->quantity }}"><i class="fas fa-edit"></i></button>
                                    <form action="{{ route('admin.recipes.destroy', $item) }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus item resep ini?')">
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
        @empty
            <div class="card"><div class="card-body text-center text-muted py-4">Belum ada resep. Tambahkan menggunakan form di samping.</div></div>
        @endforelse
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-plus-circle mr-1"></i>Tambah / Edit Resep</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.recipes.store') }}" id="recipeForm">
                    @csrf
                    <div class="form-group">
                        <label>Menu</label>
                        <select name="menu_item_id" class="form-control" required id="recipeMenu">
                            <option value="">Pilih Menu</option>
                            @foreach($menuItems as $menu)
                                <option value="{{ $menu->id }}">{{ $menu->name }} ({{ $menu->category?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Bahan Baku</label>
                        <select name="ingredient_id" class="form-control" required id="recipeIngredient">
                            <option value="">Pilih Bahan</option>
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Qty per 1 Porsi</label>
                        <input type="number" name="quantity" class="form-control" required step="0.01" min="0.01" id="recipeQty" placeholder="0.00">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save mr-1"></i>Simpan Resep</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).on('click', '.btn-edit-recipe', function() {
    $('#recipeMenu').val($(this).data('menu'));
    $('#recipeIngredient').val($(this).data('ingredient'));
    $('#recipeQty').val($(this).data('quantity'));
    $('html, body').animate({ scrollTop: $('#recipeForm').offset().top - 100 }, 300);
});
</script>
@endpush
