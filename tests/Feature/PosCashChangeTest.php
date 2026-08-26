<?php

use App\Enums\OrderType;
use App\Models\AdditionalCost;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\User;

test('kasir can store a cash order with cash received and change', function () {
    $kasir = User::factory()->kasir()->create();
    $table = RestaurantTable::factory()->create(['status' => 'kosong']);
    $menuItem = MenuItem::factory()->create(['price' => 10000]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'dine-in',
            'restaurant_table_id' => $table->id,
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2, 'notes' => '']],
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'payment_amount' => 20000,
            'cash_received' => 50000,
            'discount' => 0,
        ])
        ->assertRedirect(route('pos.index'))
        ->assertSessionHas('success_order_id');

    $order = Order::where('created_by', $kasir->id)->first();
    expect($order)->not->toBeNull();
    expect((float) $order->subtotal)->toBe(20000.0);
    expect((float) $order->tax)->toBe(0.0);
    expect((float) $order->total)->toBe(20000.0);

    $payment = Payment::where('order_id', $order->id)->first();
    expect((float) $payment->cash_received)->toBe(50000.0);
    expect((float) $payment->change)->toBe(30000.0);
});

test('kasir can store a cash order without cash received and change stays null', function () {
    $kasir = User::factory()->kasir()->create();
    $menuItem = MenuItem::factory()->create(['price' => 10000]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 1, 'notes' => '']],
            'payment_method' => 'cash',
            'payment_amount' => 11100,
            'discount' => 0,
        ])
        ->assertRedirect(route('pos.index'));

    $order = Order::where('created_by', $kasir->id)->first();
    $payment = Payment::where('order_id', $order->id)->first();

    expect($payment->cash_received)->toBeNull();
    expect($payment->change)->toBeNull();
});

test('kasir can store an order with additional cost from master data', function () {
    $kasir = User::factory()->kasir()->create();
    $menuItem = MenuItem::factory()->create(['price' => 10000]);
    $cost = AdditionalCost::factory()->create(['name' => 'Service Charge', 'rate' => 5]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2, 'notes' => '']],
            'payment_method' => 'qris',
            'payment_amount' => 21000,
            'additional_cost_id' => $cost->id,
            'discount' => 0,
        ])
        ->assertRedirect(route('pos.index'));

    $order = Order::where('created_by', $kasir->id)->first();
    expect((float) $order->subtotal)->toBe(20000.0);
    expect($order->additional_cost_name)->toBe('Service Charge');
    expect((float) $order->additional_cost_rate)->toBe(5.0);
    expect((float) $order->additional_cost)->toBe(1000.0);
    expect((float) $order->tax)->toBe(0.0);
    expect((float) $order->total)->toBe(21000.0);
});

test('additional cost is computed on amount after discount', function () {
    $kasir = User::factory()->kasir()->create();
    $menuItem = MenuItem::factory()->create(['price' => 10000]);
    $cost = AdditionalCost::factory()->create(['name' => 'Service Charge', 'rate' => 5]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2, 'notes' => '']],
            'payment_method' => 'cash',
            'payment_amount' => 18900,
            'cash_received' => 25000,
            'additional_cost_id' => $cost->id,
            'discount' => 2000,
        ])
        ->assertRedirect(route('pos.index'));

    $order = Order::where('created_by', $kasir->id)->first();
    expect((float) $order->subtotal)->toBe(20000.0);
    expect((float) $order->discount)->toBe(2000.0);
    expect((float) $order->additional_cost)->toBe(900.0);
    expect((float) $order->tax)->toBe(0.0);
    expect((float) $order->total)->toBe(18900.0);

    $payment = Payment::where('order_id', $order->id)->first();
    expect((float) $payment->change)->toBe(6100.0);
});

test('receipt includes additional cost for orders with additional cost', function () {
    $kasir = User::factory()->kasir()->create();

    $order = Order::factory()->create([
        'created_by' => $kasir->id,
        'order_type' => OrderType::DineIn,
        'subtotal' => 20000,
        'discount' => 0,
        'additional_cost_name' => 'Service Charge',
        'additional_cost_rate' => 5,
        'additional_cost' => 1000,
        'tax' => 2200,
        'total' => 23200,
    ]);

    $this->actingAs($kasir)
        ->getJson(route('pos.orders.receipt', $order))
        ->assertOk()
        ->assertJsonPath('order.additional_cost_rate', 5)
        ->assertJsonPath('order.additional_cost', 1000);
});

test('recalculateTotals applies additional cost and discount', function () {
    $cost = AdditionalCost::factory()->create(['name' => 'Service Charge', 'rate' => 5]);

    $order = Order::factory()->create([
        'subtotal' => 0,
        'discount' => 2000,
        'additional_cost_id' => $cost->id,
        'additional_cost_name' => $cost->name,
        'additional_cost_rate' => 5,
        'tax' => 0,
        'total' => 0,
    ]);

    OrderItem::factory()->count(2)->create([
        'order_id' => $order->id,
        'quantity' => 1,
        'price' => 10000,
        'subtotal' => 10000,
    ]);

    $order->recalculateTotals();

    expect((float) $order->subtotal)->toBe(20000.0);
    expect((float) $order->additional_cost)->toBe(900.0);
    expect((float) $order->tax)->toBe(0.0);
    expect((float) $order->total)->toBe(18900.0);
});

test('receipt includes cash received and change for cash payment', function () {
    $kasir = User::factory()->kasir()->create();

    $order = Order::factory()->create([
        'created_by' => $kasir->id,
        'order_type' => OrderType::DineIn,
        'subtotal' => 20000,
        'discount' => 0,
        'tax' => 2200,
        'total' => 22200,
    ]);

    Payment::factory()->create([
        'order_id' => $order->id,
        'method' => 'cash',
        'amount' => 22200,
        'cash_received' => 50000,
        'change' => 27800,
    ]);

    $this->actingAs($kasir)
        ->getJson(route('pos.orders.receipt', $order))
        ->assertOk()
        ->assertJsonPath('order.payments.0.cash_received', 50000)
        ->assertJsonPath('order.payments.0.change', 27800);
});
