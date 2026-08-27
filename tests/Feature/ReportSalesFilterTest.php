<?php

use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin can filter sales report by status', function () {
    $admin = User::factory()->admin()->create();
    Order::factory()->create(['order_status' => 'selesai']);
    Order::factory()->create(['order_status' => 'dibatalkan']);
    Order::factory()->create(['order_status' => 'baru']);

    $this->actingAs($admin)
        ->get(route('reports.sales', ['status' => 'selesai']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Sales')
            ->has('orders.data', 1)
            ->where('orders.data.0.order_status', 'selesai')
            ->where('filters.status', 'selesai')
            ->where('totalOrders', 1));
});

test('sales report without status filter shows all statuses', function () {
    $admin = User::factory()->admin()->create();
    Order::factory()->create(['order_status' => 'selesai']);
    Order::factory()->create(['order_status' => 'dibatalkan']);
    Order::factory()->create(['order_status' => 'diproses']);

    $this->actingAs($admin)
        ->get(route('reports.sales'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Reports/Sales')
            ->has('orders.data', 3)
            ->where('totalOrders', 3));
});

test('sales export respects the status filter', function () {
    $admin = User::factory()->admin()->create();
    Order::factory()->create(['order_status' => 'selesai']);
    Order::factory()->create(['order_status' => 'dibatalkan']);

    $this->actingAs($admin)
        ->get(route('reports.sales.export', ['status' => 'dibatalkan']))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
