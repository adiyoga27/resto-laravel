<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuCategoryController extends Controller
{
    public function index(): View
    {
        $categories = MenuCategory::orderBy('sort_order')->paginate(20);

        return view('admin.menu-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.menu-categories.create');
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

    public function edit(MenuCategory $menuCategory): View
    {
        return view('admin.menu-categories.edit', compact('menuCategory'));
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
