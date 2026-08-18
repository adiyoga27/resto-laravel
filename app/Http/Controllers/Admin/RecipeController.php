<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\RecipeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecipeController extends Controller
{
    public function index(Request $request): View
    {
        $menuItemId = $request->get('menu_item_id');
        $menuItems = MenuItem::with('category')->orderBy('name')->get();
        $ingredients = Ingredient::active()->orderBy('name')->get();

        $recipes = RecipeItem::with(['menuItem.category', 'ingredient'])
            ->when($menuItemId, fn ($q) => $q->where('menu_item_id', $menuItemId))
            ->orderBy('menu_item_id')
            ->get()
            ->groupBy('menu_item_id');

        return view('admin.recipes.index', compact('menuItems', 'ingredients', 'recipes', 'menuItemId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        RecipeItem::updateOrCreate(
            [
                'menu_item_id' => $validated['menu_item_id'],
                'ingredient_id' => $validated['ingredient_id'],
            ],
            ['quantity' => $validated['quantity']]
        );

        return redirect()->route('admin.recipes.index', ['menu_item_id' => $validated['menu_item_id']])
            ->with('success', 'Resep berhasil disimpan.');
    }

    public function destroy(RecipeItem $recipe): RedirectResponse
    {
        $menuItemId = $recipe->menu_item_id;
        $recipe->delete();

        return redirect()->route('admin.recipes.index', ['menu_item_id' => $menuItemId])
            ->with('success', 'Item resep berhasil dihapus.');
    }
}
