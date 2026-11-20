<?php

use App\Models\AdditionalCost;
use App\Models\User;

test('unauthenticated user cannot fetch additional costs', function () {
    $this->getJson('/api/v1/additional-costs')->assertUnauthorized();
});

test('authenticated user only lists active additional costs assigned to them', function () {
    $user = User::factory()->create();
    $active = AdditionalCost::factory()->create(['name' => 'Service Charge', 'rate' => 5, 'is_active' => true]);
    $active->users()->attach($user);
    AdditionalCost::factory()->create(['name' => 'Arsip', 'rate' => 10, 'is_active' => false])->users()->attach($user);
    AdditionalCost::factory()->create(['name' => 'Lainnya', 'rate' => 3, 'is_active' => true]);

    $this->actingAs($user)
        ->getJson('/api/v1/additional-costs')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $active->id)
        ->assertJsonPath('0.name', 'Service Charge')
        ->assertJsonPath('0.rate', '5.00');
});
