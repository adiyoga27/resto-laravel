<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;

test('unauthenticated user cannot fetch menu', function () {
    $this->getJson('/api/v1/menu')->assertUnauthorized();
});

test('authenticated user can list menu items sorted by name a-z', function () {
    $user = User::factory()->create();
    $category = MenuCategory::factory()->create(['name' => 'Makanan']);

    MenuItem::factory()->create(['menu_category_id' => $category->id, 'name' => 'Mangga', 'sort_order' => 1]);
    MenuItem::factory()->create(['menu_category_id' => $category->id, 'name' => 'Apel', 'sort_order' => 0]);
    MenuItem::factory()->create(['menu_category_id' => $category->id, 'name' => 'Zebra', 'sort_order' => 2]);

    $this->actingAs($user)
        ->getJson('/api/v1/menu')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.menu_items.0.name', 'Apel')
        ->assertJsonPath('0.menu_items.1.name', 'Mangga')
        ->assertJsonPath('0.menu_items.2.name', 'Zebra');
});

test('menu index only returns active menu items', function () {
    $user = User::factory()->create();
    $category = MenuCategory::factory()->create(['name' => 'Makanan']);

    MenuItem::factory()->create(['menu_category_id' => $category->id, 'name' => 'Aktif', 'is_active' => true]);
    MenuItem::factory()->create(['menu_category_id' => $category->id, 'name' => 'Nonaktif', 'is_active' => false]);

    $this->actingAs($user)
        ->getJson('/api/v1/menu')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonCount(1, '0.menu_items');
});
