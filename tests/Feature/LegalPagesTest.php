<?php

test('self registration through the api is not available', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Tamu',
        'email' => 'tamu@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertNotFound();

    $this->assertDatabaseMissing('users', ['email' => 'tamu@example.test']);
});

test('privacy policy is public', function () {
    $this->get('/privacy-policy')
        ->assertOk()
        ->assertSee('Kebijakan Privasi Shadja POS')
        ->assertSee('PT Coding Aja Indonesia')
        ->assertSee(route('legal.delete-account'));
});

test('delete account page is public and explains what is kept', function () {
    $this->get('/delete-account')
        ->assertOk()
        ->assertSee('Hapus Akun Shadja POS')
        ->assertSee('Data yang dihapus')
        ->assertSee('Data yang tetap disimpan');
});

test('legal pages show the support email when configured', function () {
    config(['services.support.email' => 'support@example.test']);

    $this->get('/privacy-policy')->assertSee('support@example.test');
    $this->get('/delete-account')->assertSee('mailto:support@example.test', false);
});

test('delete account page falls back to the restaurant admin without a support email', function () {
    config(['services.support.email' => null]);

    $this->get('/delete-account')
        ->assertOk()
        ->assertSee('Hubungi admin restoran Anda')
        ->assertDontSee('mailto:', false);
});
