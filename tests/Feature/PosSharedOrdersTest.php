<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('kasir can see active orders created by another kasir on the POS page', function () {
    $kasirA = User::factory()->kasir()->create();
    $kasirB = User::factory()->kasir()->create();

    $orderA = Order::factory()->create(['created_by' => $kasirA->id]);
    $orderB = Order::factory()->create(['created_by' => $kasirB->id]);
    Order::factory()->create(['created_by' => $kasirB->id, 'order_status' => OrderStatus::Selesai]);

    $this->actingAs($kasirA)
        ->get(route('pos.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pos/Index')
            ->has('activeOrders', 2)
            ->where('activeOrders.0.created_by.name', $orderA->createdBy->name)
            ->where('activeOrders.1.created_by.name', $orderB->createdBy->name)
            ->where('activeOrders.0.order_number', $orderA->order_number)
            ->where('activeOrders.1.order_number', $orderB->order_number));
});

test('kasir can complete an order created by another kasir', function () {
    $kasirA = User::factory()->kasir()->create();
    $kasirB = User::factory()->kasir()->create();

    $order = Order::factory()->create(['created_by' => $kasirA->id]);

    $this->actingAs($kasirB)
        ->post(route('pos.orders.complete', $order))
        ->assertRedirect(route('pos.index'));

    expect($order->fresh()->order_status)->toBe(OrderStatus::Selesai);
});

test('kasir can cancel an order created by another kasir', function () {
    $kasirA = User::factory()->kasir()->create();
    $kasirB = User::factory()->kasir()->create();

    $order = Order::factory()->create(['created_by' => $kasirA->id]);

    $this->actingAs($kasirB)
        ->post(route('pos.orders.cancel', $order))
        ->assertRedirect(route('pos.index'));

    expect($order->fresh()->order_status)->toBe(OrderStatus::Dibatalkan);
});
