<?php

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;

test('kasir can view receipt of own order', function () {
    $kasir = User::factory()->kasir()->create();

    $order = Order::factory()->create([
        'created_by' => $kasir->id,
        'subtotal' => 50000,
        'discount' => 0,
        'tax' => 5500,
        'total' => 55500,
    ]);

    $menuItem = MenuItem::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $menuItem->id,
    ]);
    Payment::factory()->create([
        'order_id' => $order->id,
        'amount' => 55500,
    ]);

    $this->actingAs($kasir)
        ->getJson(route('pos.orders.receipt', $order))
        ->assertOk()
        ->assertJsonPath('order.number', $order->order_number)
        ->assertJsonPath('order.total', 55500)
        ->assertJsonCount(1, 'order.items')
        ->assertJsonCount(1, 'order.payments');
});

test('kasir cannot view receipt of other kasir order', function () {
    $kasir = User::factory()->kasir()->create();
    $other = User::factory()->kasir()->create();

    $order = Order::factory()->create(['created_by' => $other->id]);

    $this->actingAs($kasir)
        ->getJson(route('pos.orders.receipt', $order))
        ->assertForbidden();
});

test('admin can view receipt of any order', function () {
    $admin = User::factory()->admin()->create();
    $kasir = User::factory()->kasir()->create();

    $order = Order::factory()->create(['created_by' => $kasir->id]);

    $this->actingAs($admin)
        ->getJson(route('pos.orders.receipt', $order))
        ->assertOk()
        ->assertJsonPath('order.number', $order->order_number);
});
