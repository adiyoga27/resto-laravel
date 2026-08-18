<?php

use App\Models\User;

test('guest cannot sign qz messages', function () {
    $this->call('POST', route('qz.sign'), [], [], [], [], 'message-to-sign')
        ->assertRedirect('/login');
});

test('authenticated user gets a valid signature', function () {
    $user = User::factory()->create();

    $message = 'QZ message to sign';

    $response = $this->actingAs($user)
        ->call('POST', route('qz.sign'), [], [], [], ['CONTENT_TYPE' => 'text/plain'], $message);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=utf-8');

    $verified = openssl_verify(
        $message,
        base64_decode($response->getContent()),
        file_get_contents(public_path('qz/digital-certificate.txt')),
        OPENSSL_ALGO_SHA512
    );

    expect($verified)->toBe(1);
});
