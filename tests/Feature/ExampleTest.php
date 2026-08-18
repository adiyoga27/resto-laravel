<?php

use App\Models\User;

test('guests hitting the root are redirected to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

test('authenticated users are redirected to their role homepage', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/');

    $response->assertRedirect(route('admin.dashboard'));
});
