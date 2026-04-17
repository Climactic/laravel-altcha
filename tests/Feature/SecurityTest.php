<?php

declare(strict_types=1);

use Climactic\Altcha\Support\Verifier;

it('rejects an expired challenge', function (): void {
    $payload = altchaPayload(expiresIn: -10);

    $result = app(Verifier::class)->verify($payload);

    expect($result->verified)->toBeFalse()
        ->and($result->reason)->toBe(Verifier::REASON_EXPIRED);
});

it('rejects a payload signed with a different secret', function (): void {
    config(['altcha.hmac_secret' => 'the-real-secret']);

    $payload = altchaPayload('a-different-secret');

    $result = app(Verifier::class)->verify($payload);

    expect($result->verified)->toBeFalse()
        ->and($result->reason)->toBe(Verifier::REASON_INVALID);
});

it('rejects a payload with a tampered signature', function (): void {
    $payload = tamperedAltchaPayload(function (array $p): array {
        $p['challenge']['signature'] = strrev((string) $p['challenge']['signature']);

        return $p;
    });

    $result = app(Verifier::class)->verify($payload);

    expect($result->verified)->toBeFalse()
        ->and($result->reason)->toBe(Verifier::REASON_INVALID);
});

it('rejects a payload with a tampered counter', function (): void {
    $payload = tamperedAltchaPayload(function (array $p): array {
        $p['solution']['counter'] = (int) $p['solution']['counter'] + 1;

        return $p;
    });

    $result = app(Verifier::class)->verify($payload);

    expect($result->verified)->toBeFalse()
        ->and($result->reason)->toBe(Verifier::REASON_INVALID);
});

it('rejects a payload with a tampered derived key', function (): void {
    $payload = tamperedAltchaPayload(function (array $p): array {
        $p['solution']['derivedKey'] = str_repeat('0', strlen((string) $p['solution']['derivedKey']));

        return $p;
    });

    $result = app(Verifier::class)->verify($payload);

    expect($result->verified)->toBeFalse()
        ->and($result->reason)->toBe(Verifier::REASON_INVALID);
});

it('rejects a payload missing required challenge fields', function (): void {
    $payload = tamperedAltchaPayload(function (array $p): array {
        unset($p['challenge']);

        return $p;
    });

    $result = app(Verifier::class)->verify($payload);

    expect($result->verified)->toBeFalse()
        ->and($result->reason)->toBe(Verifier::REASON_MALFORMED);
});
