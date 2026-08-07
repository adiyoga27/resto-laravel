<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(): View
    {
        $tables = RestaurantTable::orderBy('table_number')->paginate(20);

        return view('admin.tables.index', compact('tables'));
    }

    public function create(): View
    {
        return view('admin.tables.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50', 'unique:restaurant_tables'],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        $validated['status'] = TableStatus::Kosong;
        RestaurantTable::create($validated);

        return redirect()->route('admin.tables.index')->with('success', 'Meja berhasil dibuat.');
    }

    public function edit(RestaurantTable $table): View
    {
        $statuses = TableStatus::cases();

        return view('admin.tables.edit', compact('table', 'statuses'));
    }

    public function update(Request $request, RestaurantTable $table): RedirectResponse
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50', Rule::unique('restaurant_tables')->ignore($table->id)],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::enum(TableStatus::class)],
        ]);

        $table->update($validated);

        return redirect()->route('admin.tables.index')->with('success', 'Meja berhasil diupdate.');
    }

    public function destroy(RestaurantTable $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('admin.tables.index')->with('success', 'Meja berhasil dihapus.');
    }
}
