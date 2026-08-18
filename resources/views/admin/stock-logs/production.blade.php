@extends('layouts.app')
@section('title', 'Produksi')

@push('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.stock-logs.index') }}">Mutasi Stok</a></li>
<li class="breadcrumb-item active">Produksi</li>
@endpush

@section('content')
<div class="card border-0 shadow-sm mx-auto" style="max-width:700px;">
    <div class="card-body p-4">
        <h5 class="mb-4"><i class="fas fa-industry mr-2"></i>Produksi Menu (Bahan → Produk Jadi)</h5>

        <form method="POST" action="{{ route('admin.stock-logs.store-production') }}" id="productionForm">
            @csrf
            <div class="form-group">
                <label>Pilih Menu</label>
                <select name="menu_item_id" class="form-control" required id="menuSelect" onchange="showRecipe()">
                    <option value="">Pilih Menu untuk Diproduksi</option>
                    @foreach($menuItems as $menu)
                        <option value="{{ $menu->id }}" data-recipe='@json($menu->recipeItems->map(fn($r) => ['name' => $r->ingredient->name, 'unit' => $r->ingredient->unit, 'qty' => $r->quantity, 'stock' => $r->ingredient->current_stock]))'>
                            {{ $menu->name }} ({{ $menu->category?->name }}) — Stok Produk: {{ $menu->stock }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="recipeBox" style="display:none;" class="mb-3">
                <h6>Komposisi per 1 Porsi:</h6>
                <table class="table table-sm table-bordered" id="recipeTable">
                    <thead><tr><th>Bahan</th><th>Satuan</th><th class="text-right">Qty</th><th class="text-right">Stok Tersedia</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="form-group">
                <label>Jumlah Produksi (porsi)</label>
                <input type="number" name="quantity" class="form-control" required min="1" value="1" id="qtyInput" onchange="calcUsage()">
                <small class="text-muted">Bahan baku akan otomatis berkurang sesuai resep × jumlah produksi.</small>
            </div>

            <div id="usageBox" style="display:none;" class="mb-3">
                <h6>Total Bahan Yang Digunakan:</h6>
                <table class="table table-sm table-bordered" id="usageTable">
                    <thead><tr><th>Bahan</th><th class="text-right">Total Digunakan</th><th class="text-right">Sisa Stok</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="d-flex">
                <button type="submit" class="btn btn-warning mr-2" onclick="return confirm('Produksi sekarang? Stok bahan akan berkurang dan stok produk bertambah.')"><i class="fas fa-play mr-1"></i>Produksi</button>
                <a href="{{ route('admin.stock-logs.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var currentRecipe = [];

function showRecipe() {
    var sel = document.getElementById('menuSelect');
    var opt = sel.options[sel.selectedIndex];
    if (!opt.value) { document.getElementById('recipeBox').style.display = 'none'; document.getElementById('usageBox').style.display = 'none'; return; }

    try { currentRecipe = JSON.parse(opt.dataset.recipe); } catch(e) { currentRecipe = []; }

    var tbody = document.querySelector('#recipeTable tbody');
    tbody.innerHTML = currentRecipe.map(function(r) {
        return '<tr><td>'+r.name+'</td><td>'+r.unit+'</td><td class="text-right">'+parseFloat(r.qty).toFixed(2)+'</td><td class="text-right">'+parseFloat(r.stock).toFixed(2)+'</td></tr>';
    }).join('');

    document.getElementById('recipeBox').style.display = 'block';
    calcUsage();
}

function calcUsage() {
    if (currentRecipe.length === 0) return;
    var qty = parseInt(document.getElementById('qtyInput').value) || 1;

    var tbody = document.querySelector('#usageTable tbody');
    tbody.innerHTML = currentRecipe.map(function(r) {
        var used = parseFloat(r.qty) * qty;
        var remaining = parseFloat(r.stock) - used;
        var cls = remaining < 0 ? 'text-danger font-weight-bold' : '';
        return '<tr><td>'+r.name+'</td><td class="text-right">'+used.toFixed(2)+'</td><td class="text-right '+cls+'">'+remaining.toFixed(2)+'</td></tr>';
    }).join('');

    document.getElementById('usageBox').style.display = 'block';
}
</script>
@endpush
