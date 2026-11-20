<?php

use App\Models\AdditionalCost;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin can create an additional cost assigned to specific users', function () {
    $admin = User::factory()->admin()->create();
    $kasirA = User::factory()->kasir()->create();
    $kasirB = User::factory()->kasir()->create();

    $this->actingAs($admin)
        ->post(route('admin.additional-costs.store'), [
            'name' => 'Pajak',
            'rate' => 10,
            'is_active' => true,
            'sort_order' => 0,
            'user_ids' => [$kasirA->id],
        ])
        ->assertRedirect(route('admin.additional-costs.index'));

    $cost = AdditionalCost::where('name', 'Pajak')->first();
    expect($cost->users->pluck('id')->all())->toBe([$kasirA->id]);
    expect($kasirA->applicableAdditionalCost()?->id)->toBe($cost->id);
    expect($kasirB->applicableAdditionalCost())->toBeNull();
});

test('admin can change the users assigned to an additional cost', function () {
    $admin = User::factory()->admin()->create();
    $kasirA = User::factory()->kasir()->create();
    $kasirB = User::factory()->kasir()->create();
    $cost = AdditionalCost::factory()->create(['name' => 'Pajak', 'rate' => 10]);
    $cost->users()->attach($kasirA);

    $this->actingAs($admin)
        ->put(route('admin.additional-costs.update', $cost), [
            'name' => 'Pajak',
            'rate' => 10,
            'is_active' => true,
            'sort_order' => 0,
            'user_ids' => [$kasirB->id],
        ])
        ->assertRedirect(route('admin.additional-costs.index'));

    expect($cost->users()->pluck('users.id')->all())->toBe([$kasirB->id]);
});

test('admin edit page receives assigned user ids and assignable users', function () {
    $admin = User::factory()->admin()->create();
    $kasir = User::factory()->kasir()->create();
    $cost = AdditionalCost::factory()->create();
    $cost->users()->attach($kasir);

    $this->actingAs($admin)
        ->get(route('admin.additional-costs.edit', $cost))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/AdditionalCosts/Edit')
            ->where('userIds', [$kasir->id])
            ->has('users', 2));
});

test('mobile order API applies the additional cost assigned to the user', function () {
    $customer = User::factory()->customer()->create();
    $menuItem = MenuItem::factory()->create(['price' => 10000]);
    AdditionalCost::factory()->create(['name' => 'Pajak', 'rate' => 10])->users()->attach($customer);

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders', [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 1, 'notes' => '']],
            'customer_name' => 'John Doe',
        ])
        ->assertCreated();

    $order = Order::where('customer_id', $customer->id)->first();
    expect($order->additional_cost_name)->toBe('Pajak');
    expect((float) $order->additional_cost)->toBe(1000.0);
    expect((float) $order->total)->toBe(11000.0);
});

test('mobile order API ignores a requested additional cost not assigned to the user', function () {
    $customer = User::factory()->customer()->create();
    $menuItem = MenuItem::factory()->create(['price' => 10000]);
    $cost = AdditionalCost::factory()->create(['rate' => 10]);

    $this->actingAs($customer, 'sanctum')
        ->postJson('/api/v1/orders', [
            'order_type' => 'pickup',
            'items' => [['menu_item_id' => $menuItem->id, 'quantity' => 1, 'notes' => '']],
            'customer_name' => 'John Doe',
            'additional_cost_id' => $cost->id,
        ])
        ->assertCreated();

    $order = Order::where('customer_id', $customer->id)->first();
    expect($order->additional_cost_id)->toBeNull();
    expect((float) $order->total)->toBe(10000.0);
});
