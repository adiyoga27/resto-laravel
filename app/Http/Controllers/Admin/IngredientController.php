<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IngredientController extends Controller
{
    public function index(): Response
    {
        $ingredients = Ingredient::orderBy('name')->paginate(20);

        return Inertia::render('Admin/Ingredients/Index', [
            'ingredients' => $ingredients,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Ingredients/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'current_stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $ingredient = Ingredient::create($validated);

        ActivityLogger::log('ingredient.created', 'Membuat bahan baku "'.$ingredient->name.'"', $ingredient);

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(Ingredient $ingredient): Response
    {
        return Inertia::render('Admin/Ingredients/Edit', [
            'ingredient' => $ingredient,
        ]);
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'current_stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $ingredient->update($validated);

        ActivityLogger::log('ingredient.updated', 'Mengupdate bahan baku "'.$ingredient->name.'"', $ingredient);

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        $name = $ingredient->name;
        $ingredient->delete();

        ActivityLogger::log('ingredient.deleted', 'Menghapus bahan baku "'.$name.'"');

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }
}
