<?php

use App\Enums\OrderType;
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
            'payment_amount' => 22200,
            'cash_received' => 50000,
            'discount' => 0,
        ])
        ->assertRedirect(route('pos.index'))
        ->assertSessionHas('success_order_id');

    $order = Order::where('created_by', $kasir->id)->first();
    expect($order)->not->toBeNull();
    expect((float) $order->subtotal)->toBe(20000.0);
    expect((float) $order->tax)->toBe(2200.0);
    expect((float) $order->total)->toBe(22200.0);

    $payment = Payment::where('order_id', $order->id)->first();
    expect((float) $payment->cash_received)->toBe(50000.0);
    expect((float) $payment->change)->toBe(27800.0);
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

test('kasir can store an order with service charge applied to total', function () {
    $kasir = User::factory()->kasir()->create();
    $menuItem = MenuItem::factory()->create(['price' => 10000]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2, 'notes' => '']],
            'payment_method' => 'qris',
            'payment_amount' => 23200,
            'service_charge_rate' => 5,
            'discount' => 0,
        ])
        ->assertRedirect(route('pos.index'));

    $order = Order::where('created_by', $kasir->id)->first();
    expect((float) $order->subtotal)->toBe(20000.0);
    expect((float) $order->service_charge_rate)->toBe(5.0);
    expect((float) $order->service_charge)->toBe(1000.0);
    expect((float) $order->tax)->toBe(2200.0);
    expect((float) $order->total)->toBe(23200.0);
});

test('service charge is computed on amount after discount', function () {
    $kasir = User::factory()->kasir()->create();
    $menuItem = MenuItem::factory()->create(['price' => 10000]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 2, 'notes' => '']],
            'payment_method' => 'cash',
            'payment_amount' => 20880,
            'cash_received' => 25000,
            'service_charge_rate' => 5,
            'discount' => 2000,
        ])
        ->assertRedirect(route('pos.index'));

    $order = Order::where('created_by', $kasir->id)->first();
    expect((float) $order->subtotal)->toBe(20000.0);
    expect((float) $order->discount)->toBe(2000.0);
    expect((float) $order->service_charge)->toBe(900.0);
    expect((float) $order->tax)->toBe(1980.0);
    expect((float) $order->total)->toBe(20880.0);

    $payment = Payment::where('order_id', $order->id)->first();
    expect((float) $payment->change)->toBe(4120.0);
});

test('receipt includes service charge for orders with service rate', function () {
    $kasir = User::factory()->kasir()->create();

    $order = Order::factory()->create([
        'created_by' => $kasir->id,
        'order_type' => OrderType::DineIn,
        'subtotal' => 20000,
        'discount' => 0,
        'service_charge_rate' => 5,
        'service_charge' => 1000,
        'tax' => 2200,
        'total' => 23200,
    ]);

    $this->actingAs($kasir)
        ->getJson(route('pos.orders.receipt', $order))
        ->assertOk()
        ->assertJsonPath('order.service_charge_rate', 5)
        ->assertJsonPath('order.service_charge', 1000);
});

test('recalculateTotals applies service charge and discount', function () {
    $order = Order::factory()->create([
        'subtotal' => 0,
        'discount' => 2000,
        'service_charge_rate' => 5,
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
    expect((float) $order->service_charge)->toBe(900.0);
    expect((float) $order->tax)->toBe(1980.0);
    expect((float) $order->total)->toBe(20880.0);
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
