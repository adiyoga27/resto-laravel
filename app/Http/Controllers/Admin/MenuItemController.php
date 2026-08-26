<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuItemController extends Controller
{
    public function index(Request $request): Response
    {
        $menuItems = MenuItem::with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%'.$request->string('search').'%')
                        ->orWhere('description', 'like', '%'.$request->string('search').'%');
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('menu_category_id', $request->integer('category_id')))
            ->when($request->filled('status'), function ($query) use ($request) {
                $request->string('status')->is('active')
                    ? $query->where('is_active', true)
                    : $query->where('is_active', false);
            })
            ->orderBy('sort_order')
            ->paginate(20)
            ->withQueryString();

        $categories = MenuCategory::orderBy('sort_order')->get();

        return Inertia::render('Admin/MenuItems/Index', [
            'menuItems' => $menuItems,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'status']),
        ]);
    }

    public function create(): Response
    {
        $categories = MenuCategory::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('Admin/MenuItems/Create', [
            'categories' => $categories,
        ]);
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

    public function edit(MenuItem $menuItem): Response
    {
        $categories = MenuCategory::where('is_active', true)->orderBy('sort_order')->get();

        return Inertia::render('Admin/MenuItems/Edit', [
            'menuItem' => $menuItem,
            'categories' => $categories,
        ]);
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
