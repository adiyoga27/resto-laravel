<?php

use App\Models\Ingredient;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login page from root', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('login page renders the Auth/Login Inertia page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
});

test('authenticated admin can view the dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.name', $admin->name)
            ->has('totalOrders')
            ->has('totalRevenue')
            ->has('dailyData.labels')
            ->has('weeklyData.labels')
            ->has('monthlyData.labels'));
});

test('authenticated admin can list users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
            ->has('users.data', 4));
});

test('authenticated admin can list menu items', function () {
    $admin = User::factory()->admin()->create();
    $category = MenuCategory::factory()->create();
    MenuItem::factory()->count(3)->create(['menu_category_id' => $category->id]);

    $this->actingAs($admin)
        ->get(route('admin.menu-items.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/MenuItems/Index')
            ->has('menuItems.data', 3)
            ->has('menuItems.data.0.category'));
});

test('authenticated admin can list tables', function () {
    $admin = User::factory()->admin()->create();
    RestaurantTable::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.tables.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Tables/Index')
            ->has('tables.data', 2));
});

test('authenticated admin can list ingredients', function () {
    $admin = User::factory()->admin()->create();
    Ingredient::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.ingredients.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Ingredients/Index')
            ->has('ingredients.data', 2));
});

test('kasir can view the POS page', function () {
    $kasir = User::factory()->kasir()->create();

    $this->actingAs($kasir)
        ->get(route('pos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pos/Index')
            ->has('tables')
            ->has('menuItems'));
});

test('staff can view the kitchen panel', function () {
    $staff = User::factory()->dapur()->create();

    $this->actingAs($staff)
        ->get(route('kitchen.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Kitchen/Index'));
});

test('kasir can view sales report', function () {
    $kasir = User::factory()->kasir()->create();

    $this->actingAs($kasir)
        ->get(route('reports.sales'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Sales')
            ->has('orders.data')
            ->has('totalRevenue')
            ->has('totalOrders'));
});

test('admin can view cash flow report', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('reports.cash-flow'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Reports/CashFlow')
            ->has('entries.data'));
});
