<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        $menuItems = MenuItem::with('category')->orderBy('sort_order')->paginate(20);

        return view('admin.menu-items.index', compact('menuItems'));
    }

    public function create(): View
    {
        $categories = MenuCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.menu-items.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['integer', 'min:0'],
            'sort_order' => ['integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['is_active'] = true;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menu-images', 'public');
        }

        MenuItem::create($validated);

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu berhasil dibuat.');
    }

    public function edit(MenuItem $menuItem): View
    {
        $categories = MenuCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.menu-items.edit', compact('menuItem', 'categories'));
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $validated = $request->validate([
            'menu_category_id' => ['required', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['integer', 'min:0'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menu-images', 'public');
        }

        $menuItem->update($validated);

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu berhasil diupdate.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return redirect()->route('admin.menu-items.index')->with('success', 'Menu berhasil dihapus.');
    }
}
