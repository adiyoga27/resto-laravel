<?php

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('completing an order logs the status change with acting user', function () {
    $kasir = User::factory()->kasir()->create();
    $order = Order::factory()->create(['created_by' => $kasir->id]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.complete', $order))
        ->assertRedirect(route('pos.index'));

    expect(OrderStatusLog::count())->toBe(1);
    $log = OrderStatusLog::first();
    expect($log->order_id)->toBe($order->id);
    expect($log->user_id)->toBe($kasir->id);
    expect($log->from_status)->toBe('baru');
    expect($log->to_status)->toBe('selesai');
    expect($log->source)->toBe('pos');
});

test('cancelling an order logs the status change', function () {
    $kasir = User::factory()->kasir()->create();
    $order = Order::factory()->create(['created_by' => $kasir->id]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.cancel', $order))
        ->assertRedirect(route('pos.index'));

    expect(OrderStatusLog::first()->to_status)->toBe('dibatalkan');
});

test('kitchen status update logs the change with kitchen source', function () {
    $dapur = User::factory()->dapur()->create();
    $order = Order::factory()->create();

    $this->actingAs($dapur)
        ->patchJson(route('kitchen.orders.status', $order), ['status' => 'siap'])
        ->assertOk();

    expect(OrderStatusLog::first()->to_status)->toBe('siap');
    expect(OrderStatusLog::first()->source)->toBe('kitchen');
});

test('report status update logs the change with report source', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create();

    $this->actingAs($admin)
        ->put(route('reports.sales.update', $order), ['order_status' => 'selesai'])
        ->assertRedirect();

    expect(OrderStatusLog::first()->to_status)->toBe('selesai');
    expect(OrderStatusLog::first()->source)->toBe('report');
});

test('creating an order does not log any status change', function () {
    $kasir = User::factory()->kasir()->create();
    $menuItem = MenuItem::factory()->create(['price' => 10000]);

    $this->actingAs($kasir)
        ->post(route('pos.orders.store'), [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 1, 'notes' => '']],
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'payment_amount' => 10000,
            'discount' => 0,
        ])
        ->assertRedirect(route('pos.index'));

    expect(OrderStatusLog::count())->toBe(0);
});

test('admin can view the order activity log page', function () {
    $admin = User::factory()->admin()->create();
    $order = Order::factory()->create();
    OrderStatusLog::create([
        'order_id' => $order->id,
        'user_id' => $admin->id,
        'from_status' => 'baru',
        'to_status' => 'selesai',
        'source' => 'pos',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.order-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/OrderLogs/Index')
            ->has('logs.data', 1)
            ->has('logs.data.0.order')
            ->where('logs.data.0.to_status', 'selesai')
            ->where('logs.data.0.user.name', $admin->name));
});
