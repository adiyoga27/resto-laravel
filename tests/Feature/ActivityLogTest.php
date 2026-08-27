<?php

use App\Models\ActivityLog;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('checkout action is logged with the acting user', function () {
    $kasir = User::factory()->kasir()->create();
    $menuItem = MenuItem::factory()->create(['price' => 10000]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2, 'notes' => '']],
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'payment_amount' => 20000,
            'discount' => 0,
        ])
        ->assertRedirect(route('pos.index'));

    expect(ActivityLog::count())->toBe(1);

    $log = ActivityLog::first();
    expect($log->action)->toBe('order.checkout');
    expect($log->user_id)->toBe($kasir->id);
    expect($log->description)->toContain('Checkout order #');
    expect($log->data['total'])->toBe(20000);
});

test('editing master data logs an activity entry', function () {
    $admin = User::factory()->admin()->create();
    $category = MenuCategory::factory()->create(['name' => 'Makanan']);

    $this->actingAs($admin)
        ->put(route('admin.menu-categories.update', $category), [
            'name' => 'Minuman',
            'description' => 'Updated',
            'sort_order' => 0,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.menu-categories.index'));

    expect(ActivityLog::count())->toBe(1);

    $log = ActivityLog::first();
    expect($log->action)->toBe('menu_category.updated');
    expect($log->subject_type)->toBe(MenuCategory::class);
    expect($log->subject_id)->toBe($category->id);
    expect($log->description)->toBe('Mengupdate kategori "Minuman"');
});

test('deleting master data logs an activity entry', function () {
    $admin = User::factory()->admin()->create();
    $category = MenuCategory::factory()->create(['name' => 'Makanan']);

    $this->actingAs($admin)
        ->delete(route('admin.menu-categories.destroy', $category))
        ->assertRedirect(route('admin.menu-categories.index'));

    expect(ActivityLog::first()->action)->toBe('menu_category.deleted');
    expect(ActivityLog::first()->description)->toBe('Menghapus kategori "Makanan"');
});

test('login and logout are logged', function () {
    $user = User::factory()->admin()->create(['password' => bcrypt('password123')]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ])->assertRedirect();

    expect(ActivityLog::latest('id')->first()->action)->toBe('auth.login');

    $this->actingAs($user)->post(route('logout'));

    expect(ActivityLog::latest('id')->first()->action)->toBe('auth.logout');
});

test('completing an order logs the activity with acting user', function () {
    $kasir = User::factory()->kasir()->create();
    $order = Order::factory()->create(['created_by' => $kasir->id]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.complete', $order))
        ->assertRedirect(route('pos.index'));

    $log = ActivityLog::where('action', 'order.completed')->first();
    expect($log->user_id)->toBe($kasir->id);
    expect($log->subject_id)->toBe($order->id);
});

test('admin can view the activity log page with logs and filters', function () {
    $admin = User::factory()->admin()->create();
    ActivityLog::factory()->count(2)->create(['user_id' => $admin->id]);
    ActivityLog::create([
        'user_id' => $admin->id,
        'action' => 'order.checkout',
        'description' => 'Checkout order #ORD-TEST',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.activity-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ActivityLogs/Index')
            ->has('logs.data', 3)
            ->has('users')
            ->has('actionGroups')
            ->where('logs.data.0.description', 'Checkout order #ORD-TEST'));

    $this->actingAs($admin)
        ->get(route('admin.activity-logs.index', ['action' => 'order.checkout']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ActivityLogs/Index')
            ->has('logs.data', 1)
            ->where('logs.data.0.action', 'order.checkout'));
});

test('updating a user logs the activity with changed values', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->kasir()->create(['name' => 'Budi', 'is_active' => true]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Budi Setiawan',
            'email' => $user->email,
            'role' => 'kasir',
            'phone' => $user->phone,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.users.index'));

    $log = ActivityLog::latest('id')->first();
    expect($log->action)->toBe('user.updated');
    expect($log->subject_id)->toBe($user->id);
    expect($log->data['name']['from'])->toBe('Budi');
    expect($log->data['name']['to'])->toBe('Budi Setiawan');
});

test('admin can export the activity log to excel', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.activity-logs.export'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('guest cannot access the activity log page', function () {
    $this->get('/admin/activity-logs')->assertRedirect(route('login'));
});
