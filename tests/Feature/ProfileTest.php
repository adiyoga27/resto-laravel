<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected to login when visiting profile pages', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
    $this->get(route('profile.password'))->assertRedirect(route('login'));
});

test('user can view their profile edit page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Edit')
            ->where('user.name', $admin->name)
            ->where('user.email', $admin->email));
});

test('user can update their own profile', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => $admin->email,
            'phone' => '081234567890',
        ])
        ->assertRedirect(route('profile.edit'));

    expect($admin->fresh())
        ->name->toBe('Nama Baru')
        ->phone->toBe('081234567890');
});

test('user cannot update email to one already in use', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->kasir()->create();

    $this->actingAs($admin)
        ->put(route('profile.update'), [
            'name' => $admin->name,
            'email' => $other->email,
        ])
        ->assertSessionHasErrors('email');

    expect($admin->fresh()->email)->toBe($admin->email);
});

test('user can view their change password page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('profile.password'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Profile/Password'));
});

test('user can change their password with valid current password', function () {
    $admin = User::factory()->admin()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($admin)
        ->put(route('profile.password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('profile.password'));

    expect(Hash::check('new-password', $admin->fresh()->password))->toBeTrue();
});

test('user cannot change password with wrong current password', function () {
    $admin = User::factory()->admin()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($admin)
        ->put(route('profile.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('old-password', $admin->fresh()->password))->toBeTrue();
});

test('kasir can access profile pages', function () {
    $kasir = User::factory()->kasir()->create();

    $this->actingAs($kasir)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Profile/Edit'));

    $this->actingAs($kasir)
        ->get(route('profile.password'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Profile/Password'));
});
