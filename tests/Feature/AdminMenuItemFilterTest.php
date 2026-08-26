<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin can search menu items by name or description', function () {
    $admin = User::factory()->admin()->create();
    MenuItem::factory()->create(['name' => 'Nasi Goreng Spesial']);
    MenuItem::factory()->create(['name' => 'Mie Ayam', 'description' => 'Dengan telur spesial']);

    $this->actingAs($admin)
        ->get(route('admin.menu-items.index', ['search' => 'spesial']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/MenuItems/Index')
            ->has('menuItems.data', 2)
            ->where('filters.search', 'spesial'));
});

test('admin can filter menu items by category', function () {
    $admin = User::factory()->admin()->create();
    $categoryA = MenuCategory::factory()->create();
    $categoryB = MenuCategory::factory()->create();
    MenuItem::factory()->create(['menu_category_id' => $categoryA->id]);
    MenuItem::factory()->count(2)->create(['menu_category_id' => $categoryB->id]);

    $this->actingAs($admin)
        ->get(route('admin.menu-items.index', ['category_id' => $categoryA->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/MenuItems/Index')
            ->has('menuItems.data', 1)
            ->where('filters.category_id', (string) $categoryA->id));
});

test('admin can filter menu items by status', function () {
    $admin = User::factory()->admin()->create();
    MenuItem::factory()->create(['is_active' => true]);
    MenuItem::factory()->create(['is_active' => false]);

    $this->actingAs($admin)
        ->get(route('admin.menu-items.index', ['status' => 'inactive']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/MenuItems/Index')
            ->has('menuItems.data', 1)
            ->where('menuItems.data.0.is_active', false)
            ->where('filters.status', 'inactive'));
});
