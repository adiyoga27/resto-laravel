<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuCategoryController extends Controller
{
    public function index(): Response
    {
        $categories = MenuCategory::withCount('menuItems')->orderBy('sort_order')->paginate(20);

        return Inertia::render('Admin/MenuCategories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/MenuCategories/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $validated['is_active'] = true;
        MenuCategory::create($validated);

        return redirect()->route('admin.menu-categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(MenuCategory $menuCategory): Response
    {
        return Inertia::render('Admin/MenuCategories/Edit', [
            'menuCategory' => $menuCategory,
        ]);
    }

    public function update(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $menuCategory->update($validated);

        return redirect()->route('admin.menu-categories.index')->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(MenuCategory $menuCategory): RedirectResponse
    {
        $menuCategory->delete();

        return redirect()->route('admin.menu-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
