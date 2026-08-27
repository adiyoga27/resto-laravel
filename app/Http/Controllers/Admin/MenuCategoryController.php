<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Services\ActivityLogger;
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
        $category = MenuCategory::create($validated);

        ActivityLogger::log('menu_category.created', 'Membuat kategori "'.$category->name.'"', $category);

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

        ActivityLogger::log('menu_category.updated', 'Mengupdate kategori "'.$menuCategory->name.'"', $menuCategory);

        return redirect()->route('admin.menu-categories.index')->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(MenuCategory $menuCategory): RedirectResponse
    {
        $name = $menuCategory->name;
        $menuCategory->delete();

        ActivityLogger::log('menu_category.deleted', 'Menghapus kategori "'.$name.'"');

        return redirect()->route('admin.menu-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
