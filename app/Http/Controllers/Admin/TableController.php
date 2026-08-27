<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TableController extends Controller
{
    public function index(): Response
    {
        $tables = RestaurantTable::orderBy('table_number')->paginate(20);

        return Inertia::render('Admin/Tables/Index', [
            'tables' => $tables,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Tables/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50', 'unique:restaurant_tables'],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        $validated['status'] = TableStatus::Kosong;
        $table = RestaurantTable::create($validated);

        ActivityLogger::log('table.created', 'Membuat meja "'.$table->table_number.'"', $table);

        return redirect()->route('admin.tables.index')->with('success', 'Meja berhasil dibuat.');
    }

    public function edit(RestaurantTable $table): Response
    {
        $statuses = collect(TableStatus::cases())->map(fn ($status) => [
            'value' => $status->value,
            'label' => match ($status) {
                TableStatus::Kosong => 'Kosong',
                TableStatus::Terisi => 'Terisi',
                TableStatus::Direservasi => 'Direservasi',
            },
        ]);

        return Inertia::render('Admin/Tables/Edit', [
            'table' => $table,
            'statuses' => $statuses,
        ]);
    }

    public function update(Request $request, RestaurantTable $table): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50', Rule::unique('restaurant_tables')->ignore($table->id)],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::enum(TableStatus::class)],
        ]);

        $table->update($validated);

        ActivityLogger::log('table.updated', 'Mengupdate meja "'.$table->table_number.'"', $table);

        return redirect()->route('admin.tables.index')->with('success', 'Meja berhasil diupdate.');
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        $number = $table->table_number;
        $table->delete();

        ActivityLogger::log('table.deleted', 'Menghapus meja "'.$number.'"');

        return redirect()->route('admin.tables.index')->with('success', 'Meja berhasil dihapus.');
    }
}
