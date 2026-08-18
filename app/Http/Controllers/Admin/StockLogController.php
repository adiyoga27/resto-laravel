<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockLogController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $logs = StockLog::with(['ingredient', 'menuItem', 'user'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.stock-logs.index', compact('logs', 'startDate', 'endDate'));
    }

    public function create(Request $request): View
    {
        $type = $request->get('type', 'in');
        $ingredients = Ingredient::active()->orderBy('name')->get();
        $menuItems = MenuItem::with('category')->active()->orderBy('name')->get();

        return view('admin.stock-logs.create', compact('ingredients', 'menuItems', 'type'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:in,out,adjustment'],
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $ingredient = Ingredient::findOrFail($validated['ingredient_id']);
        $stockBefore = $ingredient->current_stock;

        if ($validated['type'] === 'in') {
            $stockAfter = $stockBefore + $validated['quantity'];
        } elseif ($validated['type'] === 'out') {
            $stockAfter = $stockBefore - $validated['quantity'];
        } else {
            $stockAfter = $validated['quantity'];
        }

        StockLog::create([
            'ingredient_id' => $ingredient->id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
            'user_id' => auth()->id(),
        ]);

        $ingredient->update(['current_stock' => $stockAfter]);

        return redirect()->route('admin.stock-logs.index')
            ->with('success', 'Mutasi stok berhasil dicatat.');
    }

    public function createProduction(): View
    {
        $menuItems = MenuItem::with(['category', 'recipeItems.ingredient'])->active()->orderBy('name')->get();

        return view('admin.stock-logs.production', compact('menuItems'));
    }

    public function storeProduction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $menuItem = MenuItem::with('recipeItems.ingredient')->findOrFail($validated['menu_item_id']);

        if ($menuItem->recipeItems->isEmpty()) {
            return redirect()->back()->with('error', 'Menu ini belum memiliki resep. Silakan tambahkan resep terlebih dahulu.');
        }

        foreach ($menuItem->recipeItems as $recipe) {
            $ingredient = $recipe->ingredient;
            $totalUsed = $recipe->quantity * $validated['quantity'];
            $stockBefore = $ingredient->current_stock;
            $stockAfter = $stockBefore - $totalUsed;

            StockLog::create([
                'ingredient_id' => $ingredient->id,
                'menu_item_id' => $menuItem->id,
                'type' => 'production',
                'quantity' => $totalUsed,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference' => 'Produksi '.$menuItem->name.' x'.$validated['quantity'],
                'user_id' => auth()->id(),
            ]);

            $ingredient->update(['current_stock' => $stockAfter]);
        }

        $menuItem->increment('stock', $validated['quantity']);

        return redirect()->route('admin.stock-logs.index')
            ->with('success', 'Produksi '.$menuItem->name.' x'.$validated['quantity'].' berhasil. Stok produk bertambah.');
    }
}
