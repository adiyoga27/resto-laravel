<?php

use App\Models\StoreProfile;
use App\Models\User;

test('unauthenticated user cannot fetch store profile', function () {
    $this->getJson('/api/v1/store-profiles')->assertUnauthorized();
});

test('kasir cannot access store profile endpoints', function () {
    $kasir = User::factory()->kasir()->create();

    $this->actingAs($kasir)
        ->getJson('/api/v1/store-profiles')
        ->assertForbidden();
});

test('admin receives not found when no store profile exists', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/api/v1/store-profiles')
        ->assertNotFound()
        ->assertJsonPath('message', 'Store profile not found.');
});

test('admin can fetch the store profile', function () {
    $admin = User::factory()->admin()->create();
    StoreProfile::factory()->create(['store_name' => 'Resto Nusantara']);

    $this->actingAs($admin)
        ->getJson('/api/v1/store-profiles')
        ->assertOk()
        ->assertJsonPath('store_name', 'Resto Nusantara');
});

test('admin can create a store profile', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson('/api/v1/store-profiles', [
            'store_name' => 'Resto Nusantara',
            'store_address' => 'Jl. Merdeka No. 123, Jakarta',
            'store_phone' => '021-555-0123',
        ])
        ->assertCreated()
        ->assertJsonPath('store_name', 'Resto Nusantara')
        ->assertJsonPath('is_active', true);

    $this->assertDatabaseCount('store_profiles', 1);
    $this->assertDatabaseHas('store_profiles', [
        'store_name' => 'Resto Nusantara',
        'store_phone' => '021-555-0123',
    ]);
});

test('admin cannot create a second store profile', function () {
    $admin = User::factory()->admin()->create();
    StoreProfile::factory()->create();

    $this->actingAs($admin)
        ->postJson('/api/v1/store-profiles', [
            'store_name' => 'Resto Lain',
            'store_address' => 'Jl. Jendral Sudirman No. 1',
            'store_phone' => '021-111-1111',
        ])
        ->assertStatus(409);

    $this->assertDatabaseCount('store_profiles', 1);
});

test('admin can update the store profile', function () {
    $admin = User::factory()->admin()->create();
    StoreProfile::factory()->create([
        'store_name' => 'Resto Nusantara',
        'store_phone' => '021-555-0123',
    ]);

    $this->actingAs($admin)
        ->putJson('/api/v1/store-profiles', [
            'store_name' => 'Resto Nusantara 2',
            'store_address' => 'Jl. Sudirman No. 90, Jakarta',
            'store_phone' => '021-555-0456',
            'is_active' => false,
        ])
        ->assertOk()
        ->assertJsonPath('store_name', 'Resto Nusantara 2')
        ->assertJsonPath('is_active', false);

    $this->assertDatabaseCount('store_profiles', 1);
    $this->assertDatabaseHas('store_profiles', [
        'store_name' => 'Resto Nusantara 2',
        'store_phone' => '021-555-0456',
    ]);
});

test('admin can create via update endpoint when no profile exists', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->putJson('/api/v1/store-profiles', [
            'store_name' => 'Resto Nusantara',
            'store_address' => 'Jl. Merdeka No. 123, Jakarta',
            'store_phone' => '021-555-0123',
        ])
        ->assertCreated()
        ->assertJsonPath('store_name', 'Resto Nusantara');

    $this->assertDatabaseCount('store_profiles', 1);
});

test('admin cannot create without required fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson('/api/v1/store-profiles', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['store_name', 'store_address', 'store_phone']);
});
