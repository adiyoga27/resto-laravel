@extends('layouts.app')
@section('title', 'Edit Menu')

@push('styles')
<style>
    .menu-preview {
        width: 160px;
        height: 160px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        display: block;
    }
    #imagePreviewWrap { position: relative; }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm mx-auto" style="max-width:600px;">
    <div class="card-body p-4">
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.menu-items.update', $menuItem) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- Foto dengan preview --}}
            <div class="mb-4 text-center">
                <label class="form-label fw-semibold">Foto Menu</label>
                <div id="imagePreviewWrap" class="mx-auto">
                    <img id="imagePreview"
                         src="{{ $menuItem->image ? asset('storage/'.$menuItem->image) : '' }}"
                         class="menu-preview mx-auto"
                         alt="Preview"
                         @if(!$menuItem->image) style="display:none;" @endif>
                    <div id="noImagePlaceholder" class="menu-preview mx-auto d-flex align-items-center justify-content-center text-muted" @if($menuItem->image) style="display:none;" @endif>
                        <i class="bi bi-image" style="font-size:2rem;"></i>
                    </div>
                </div>
                <input type="file" name="image" id="imageInput" class="form-control mt-3" accept="image/*">
                <div class="form-text text-muted small">Format: JPG/PNG, maks 2MB.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                <select name="menu_category_id" class="form-select" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('menu_category_id', $menuItem->menu_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Menu <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $menuItem->name) }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $menuItem->description) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="price" value="{{ old('price', $menuItem->price) }}" step="0.01" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Stok</label>
                    <input type="number" name="stock" value="{{ old('stock', $menuItem->stock) }}" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Urutan Tampil</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $menuItem->sort_order) }}" class="form-control">
            </div>

            <div class="form-check form-switch mb-4">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $menuItem->is_active) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="is_active">Menu aktif / tampil di POS</label>
            </div>

            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="bi bi-check-lg mr-1"></i>Update</button>
                <a href="{{ route('admin.menu-items.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var input = document.getElementById('imageInput');
    var preview = document.getElementById('imagePreview');
    var placeholder = document.getElementById('noImagePlaceholder');

    input.addEventListener('change', function(){
        var file = this.files && this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e){
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush
