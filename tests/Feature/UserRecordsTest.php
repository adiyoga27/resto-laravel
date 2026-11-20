<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

function seedRecordsFor(User $user): void
{
    $now = now();

    DB::table('cash_flows')->insert([
        'date' => $now->toDateString(), 'description' => 'Kas masuk', 'type' => 'income', 'amount' => 50000,
        'created_by' => $user->id, 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('stock_logs')->insert([
        'type' => 'in', 'quantity' => 2, 'user_id' => $user->id, 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('stock_opnames')->insert([
        'date' => $now->toDateString(), 'user_id' => $user->id, 'created_at' => $now, 'updated_at' => $now,
    ]);
    $tableId = DB::table('restaurant_tables')->insertGetId([
        'name' => 'Meja 1', 'capacity' => 4, 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('reservations')->insert([
        'restaurant_table_id' => $tableId, 'customer_id' => $user->id, 'reservation_time' => $now,
        'guest_count' => 2, 'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('mobile_sync_logs')->insert([
        'user_id' => $user->id, 'device_id' => 'tablet', 'idempotency_key' => 'key-1', 'action_type' => 'order',
        'payload' => '{}', 'created_at' => $now, 'updated_at' => $now,
    ]);
}

test('deleting a user keeps their business records', function () {
    $staff = User::factory()->kasir()->create();
    seedRecordsFor($staff);

    $this->actingAs(User::factory()->admin()->create())
        ->delete(route('admin.users.destroy', $staff))
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    $this->assertDatabaseHas('cash_flows', ['description' => 'Kas masuk', 'created_by' => null]);
    $this->assertDatabaseHas('stock_logs', ['type' => 'in', 'user_id' => null]);
    $this->assertDatabaseCount('stock_opnames', 1);
    $this->assertDatabaseHas('reservations', ['guest_count' => 2, 'customer_id' => null]);
    $this->assertDatabaseHas('mobile_sync_logs', ['idempotency_key' => 'key-1', 'user_id' => null]);
});

test('deactivating a user ends their sessions and tokens but keeps their records', function () {
    $staff = User::factory()->kasir()->create();
    seedRecordsFor($staff);
    $staff->createToken('mobile');
    DB::table('sessions')->insert([
        'id' => 'session-1', 'user_id' => $staff->id, 'payload' => '', 'last_activity' => time(),
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update', $staff), [
            'name' => $staff->name, 'email' => $staff->email, 'role' => 'kasir', 'is_active' => false,
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($staff->refresh()->is_active)->toBeFalse()
        ->and($staff->tokens()->count())->toBe(0);
    $this->assertDatabaseMissing('sessions', ['id' => 'session-1']);
    $this->assertDatabaseHas('cash_flows', ['created_by' => $staff->id]);
});

test('updating an active user keeps their tokens', function () {
    $staff = User::factory()->kasir()->create();
    $staff->createToken('mobile');

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('admin.users.update', $staff), [
            'name' => 'Nama Baru', 'email' => $staff->email, 'role' => 'kasir', 'is_active' => true,
        ]);

    expect($staff->tokens()->count())->toBe(1);
});

test('admin cannot deactivate their own account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'admin', 'is_active' => false,
        ]);

    expect($admin->refresh()->is_active)->toBeTrue();
});

test('deactivated user cannot log in through the api', function () {
    $staff = User::factory()->kasir()->create(['is_active' => false, 'password' => 'password123']);

    $this->postJson('/api/v1/auth/login', ['email' => $staff->email, 'password' => 'password123'])
        ->assertForbidden();
});
