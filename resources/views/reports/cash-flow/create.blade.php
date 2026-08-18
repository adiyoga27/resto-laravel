@extends('layouts.app')
@section('title', 'Tambah Entri Arus Kas')

@push('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('reports.cash-flow') }}">Arus Kas</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endpush

@section('content')
<div class="card border-0 shadow-sm mx-auto" style="max-width:600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('reports.cash-flow.store') }}">
            @csrf
            <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="date" class="form-control" required value="{{ old('date', now()->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>Keterangan</label>
                <input type="text" name="description" class="form-control" required value="{{ old('description') }}" placeholder="Contoh: Penjualan harian, Bayar listrik...">
            </div>
            <div class="form-group">
                <label>Tipe</label>
                <select name="type" class="form-control" required>
                    <option value="debit" {{ old('type') === 'debit' ? 'selected' : '' }}>Debit (Uang Masuk)</option>
                    <option value="kredit" {{ old('type') === 'kredit' ? 'selected' : '' }}>Kredit (Uang Keluar)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah (Rp)</label>
                <input type="number" name="amount" class="form-control" required min="0.01" step="0.01" value="{{ old('amount') }}" placeholder="0">
            </div>
            <div class="form-group">
                <label>Referensi (opsional)</label>
                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="No. nota / invoice">
            </div>
            <div class="d-flex">
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-save mr-1"></i>Simpan</button>
                <a href="{{ route('reports.cash-flow') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
