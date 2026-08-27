<?php

use App\Enums\OrderType;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\User;

test('priceForOrderType uses takeaway price for pickup order when set', function () {
    $item = MenuItem::factory()->create(['price' => 15000, 'takeaway_price' => 18000]);

    expect($item->priceForOrderType('pickup'))->toBe(18000.0);
});

test('priceForOrderType falls back to regular price for pickup when takeaway price is empty', function () {
    $item = MenuItem::factory()->create(['price' => 15000, 'takeaway_price' => null]);

    expect($item->priceForOrderType('pickup'))->toBe(15000.0);
});

test('priceForOrderType uses regular price for non-pickup orders', function () {
    $item = MenuItem::factory()->create(['price' => 15000, 'takeaway_price' => 18000]);

    expect($item->priceForOrderType('dine-in'))->toBe(15000.0);
    expect($item->priceForOrderType('delivery'))->toBe(15000.0);
});

test('admin can create menu item with takeaway price', function () {
    $admin = User::factory()->admin()->create();
    $category = MenuCategory::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.menu-items.store'), [
            'menu_category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'price' => 15000,
            'takeaway_price' => 18000,
            'stock' => 10,
            'sort_order' => 0,
        ])
        ->assertRedirect(route('admin.menu-items.index'));

    $this->assertDatabaseHas('menu_items', [
        'name' => 'Nasi Goreng',
        'price' => 15000,
        'takeaway_price' => 18000,
    ]);
});

test('paying cashier pickup order uses takeaway price', function () {
    $kasir = User::factory()->kasir()->create();
    $menuItem = MenuItem::factory()->create(['price' => 15000, 'takeaway_price' => 18000]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2, 'notes' => '']],
            'payment_method' => 'cash',
            'payment_amount' => 36000,
            'discount' => 0,
        ])
        ->assertRedirect(route('pos.index'));

    $order = Order::where('created_by', $kasir->id)->first();
    expect((float) $order->subtotal)->toBe(36000.0);
    expect((float) $order->total)->toBe(36000.0);

    $orderItem = OrderItem::where('order_id', $order->id)->first();
    expect((float) $orderItem->price)->toBe(18000.0);
    expect((float) $orderItem->subtotal)->toBe(36000.0);
});

test('cashier dine-in order uses regular price even when takeaway price is set', function () {
    $kasir = User::factory()->kasir()->create();
    $table = RestaurantTable::factory()->create(['status' => 'kosong']);
    $menuItem = MenuItem::factory()->create(['price' => 15000, 'takeaway_price' => 18000]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'dine-in',
            'restaurant_table_id' => $table->id,
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 1, 'notes' => '']],
            'payment_method' => 'cash',
            'payment_amount' => 15000,
            'discount' => 0,
        ])
        ->assertRedirect(route('pos.index'));

    $order = Order::where('created_by', $kasir->id)->first();
    expect((float) $order->subtotal)->toBe(15000.0);

    $orderItem = OrderItem::where('order_id', $order->id)->first();
    expect((float) $orderItem->price)->toBe(15000.0);
});

test('mobile order API uses takeaway price for pickup order', function () {
    $customer = User::factory()->customer()->create();
    $menuItem = MenuItem::factory()->create(['price' => 15000, 'takeaway_price' => 18000]);

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders', [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 1, 'notes' => '']],
            'customer_name' => 'John Doe',
        ])
        ->assertCreated()
        ->assertJsonPath('order_items.0.price', '18000.00');

    $order = Order::where('customer_id', $customer->id)->first();
    expect($order->order_type)->toBe(OrderType::Pickup);
    expect((float) $order->subtotal)->toBe(18000.0);
});
