<?php

declare(strict_types=1);

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware(['web', 'altcha'])
        ->post('/test-altcha', fn () => response()->json(['ok' => true]));
});

it('rejects POST requests missing the altcha payload', function (): void {
    $this->post('/test-altcha', [])
        ->assertSessionHasErrors('altcha');
});

it('rejects malformed altcha payloads', function (): void {
    $this->post('/test-altcha', ['altcha' => 'not-base64-json'])
        ->assertSessionHasErrors('altcha');
});

it('accepts requests with a valid altcha payload', function (): void {
    $this->post('/test-altcha', ['altcha' => altchaPayload()])
        ->assertSuccessful()
        ->assertJson(['ok' => true]);
});

it('prevents replay of the same payload', function (): void {
    $payload = altchaPayload();

    $this->post('/test-altcha', ['altcha' => $payload])->assertSuccessful();

    $this->post('/test-altcha', ['altcha' => $payload])
        ->assertSessionHasErrors('altcha');
});

it('bypasses verification when altcha is disabled', function (): void {
    config(['altcha.enabled' => false]);

    $this->post('/test-altcha', [])->assertSuccessful();
});

it('bypasses verification for non-POST methods', function (): void {
    Route::middleware(['web', 'altcha'])
        ->get('/test-altcha-get', fn () => response()->json(['ok' => true]));

    $this->get('/test-altcha-get')->assertSuccessful();
});

it('honors a custom field name', function (): void {
    config(['altcha.field' => 'captcha']);

    $this->post('/test-altcha', ['captcha' => altchaPayload()])
        ->assertSuccessful();
});

it('bypasses verification for authenticated users', function (): void {
    $this->actingAs(new User)
        ->post('/test-altcha', [])
        ->assertSuccessful();
});

it('returns a missing-challenge message when no payload is sent', function (): void {
    $this->followingRedirects()
        ->from('/origin')
        ->post('/test-altcha', [])
        ->assertSessionHasErrors(['altcha' => 'Please complete the security challenge.']);
});

it('returns a replay message when the payload was already used', function (): void {
    $payload = altchaPayload();
    $this->post('/test-altcha', ['altcha' => $payload])->assertSuccessful();

    $this->post('/test-altcha', ['altcha' => $payload])
        ->assertSessionHasErrors(['altcha' => 'Security challenge has already been used. Please try again.']);
});

it('returns an expired message when the challenge is past its expiry', function (): void {
    $payload = altchaPayload(expiresIn: -10);

    $this->post('/test-altcha', ['altcha' => $payload])
        ->assertSessionHasErrors(['altcha' => 'Security challenge expired. Please try again.']);
});

it('returns a generic verification failure message on invalid payloads', function (): void {
    $this->post('/test-altcha', ['altcha' => 'not-base64-json'])
        ->assertSessionHasErrors(['altcha' => 'Security challenge verification failed. Please try again.']);
});

it('uses the configured cache store for replay prevention', function (): void {
    config(['cache.stores.altcha_store' => ['driver' => 'array', 'serialize' => false]]);
    config(['altcha.cache_store' => 'altcha_store']);

    $payload = altchaPayload();
    $this->post('/test-altcha', ['altcha' => $payload])->assertSuccessful();

    expect(Cache::store('altcha_store')->getMultiple([]))->toBeIterable();

    Cache::store('altcha_store')->flush();

    $this->post('/test-altcha', ['altcha' => $payload])->assertSuccessful();
});
