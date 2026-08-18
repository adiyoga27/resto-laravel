<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\StockLog;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockOpnameController extends Controller
{
    public function index(): Response
    {
        $opnames = StockOpname::with('user')->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(20);

        return Inertia::render('Admin/StockOpnames/Index', [
            'opnames' => $opnames,
        ]);
    }

    public function create(): Response
    {
        $ingredients = Ingredient::active()->orderBy('name')->get();

        return Inertia::render('Admin/StockOpnames/Create', [
            'ingredients' => $ingredients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'actual_stock' => ['required', 'array'],
            'actual_stock.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $opname = StockOpname::create([
            'date' => $validated['date'],
            'notes' => $validated['notes'],
            'status' => 'draft',
            'user_id' => auth()->id(),
        ]);

        foreach ($validated['actual_stock'] as $ingredientId => $actualStock) {
            if ($actualStock === null || $actualStock === '') {
                continue;
            }

            $ingredient = Ingredient::find($ingredientId);
            if (! $ingredient) {
                continue;
            }

            StockOpnameItem::create([
                'stock_opname_id' => $opname->id,
                'ingredient_id' => $ingredientId,
                'system_stock' => $ingredient->current_stock,
                'actual_stock' => $actualStock,
                'difference' => $actualStock - $ingredient->current_stock,
            ]);
        }

        return redirect()->route('admin.stock-opnames.show', $opname)
            ->with('success', 'Stok opname berhasil dibuat.');
    }

    public function show(StockOpname $stockOpname): Response
    {
        $stockOpname->load(['items.ingredient', 'user']);

        return Inertia::render('Admin/StockOpnames/Show', [
            'stockOpname' => $stockOpname,
        ]);
    }

    public function post(StockOpname $stockOpname): RedirectResponse
    {
        if (! $stockOpname->isDraft()) {
            return redirect()->back()->with('error', 'Stok opname sudah diposting.');
        }

        foreach ($stockOpname->items as $item) {
            $ingredient = $item->ingredient;

            StockLog::create([
                'ingredient_id' => $ingredient->id,
                'type' => 'opname',
                'quantity' => abs($item->difference),
                'stock_before' => $item->system_stock,
                'stock_after' => $item->actual_stock,
                'reference' => 'Opname #'.$stockOpname->id,
                'notes' => 'Penyesuaian stok opname',
                'user_id' => auth()->id(),
            ]);

            $ingredient->update(['current_stock' => $item->actual_stock]);
        }

        $stockOpname->update(['status' => 'posted']);

        return redirect()->route('admin.stock-opnames.show', $stockOpname)
            ->with('success', 'Stok opname berhasil diposting. Stok bahan baku telah disesuaikan.');
    }

    public function destroy(StockOpname $stockOpname): RedirectResponse
    {
        if (! $stockOpname->isDraft()) {
            return redirect()->back()->with('error', 'Stok opname yang sudah diposting tidak dapat dihapus.');
        }

        $stockOpname->items()->delete();
        $stockOpname->delete();

        return redirect()->route('admin.stock-opnames.index')
            ->with('success', 'Stok opname berhasil dihapus.');
    }
}
