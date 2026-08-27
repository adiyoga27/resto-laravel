<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('updating menu item without a new image preserves the existing image', function () {
    $admin = User::factory()->admin()->create();
    $category = MenuCategory::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'menu_category_id' => $category->id,
        'name' => 'Nasi Goreng',
        'price' => 10000,
        'image' => 'menu-images/old.jpg',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.menu-items.update', $menuItem), [
            'menu_category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'price' => 15000,
            'image' => null,
        ])
        ->assertRedirect(route('admin.menu-items.index'));

    $this->assertDatabaseHas('menu_items', [
        'id' => $menuItem->id,
        'price' => 15000,
        'image' => 'menu-images/old.jpg',
    ]);
});

test('updating menu item with a new image replaces the existing image', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $category = MenuCategory::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'menu_category_id' => $category->id,
        'name' => 'Nasi Goreng',
        'price' => 10000,
        'image' => 'menu-images/old.jpg',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.menu-items.update', $menuItem), [
            'menu_category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'price' => 15000,
            'image' => UploadedFile::fake()->image('baru.jpg'),
        ])
        ->assertRedirect(route('admin.menu-items.index'));

    $this->assertDatabaseHas('menu_items', [
        'id' => $menuItem->id,
        'price' => 15000,
    ]);

    $updated = MenuItem::find($menuItem->id);
    $this->assertNotNull($updated->image);
    $this->assertNotSame('menu-images/old.jpg', $updated->image);
    Storage::disk('public')->assertExists($updated->image);
});
