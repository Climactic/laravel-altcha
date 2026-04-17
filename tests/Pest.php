<?php

declare(strict_types=1);

use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\SolveChallengeOptions;
use Climactic\Altcha\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function altchaPayload(string $secret = 'test-secret', int $cost = 500, int $expiresIn = 300): string
{
    return altchaPayloadArray($secret, $cost, $expiresIn)['encoded'];
}

/**
 * @return array{encoded: string, payload: array<string, mixed>}
 */
function altchaPayloadArray(string $secret = 'test-secret', int $cost = 500, int $expiresIn = 300): array
{
    $algorithm = new Pbkdf2;
    $altcha = new Altcha(hmacSignatureSecret: $secret);

    $challenge = $altcha->createChallenge(new CreateChallengeOptions(
        algorithm: $algorithm,
        cost: $cost,
        expiresAt: time() + $expiresIn,
    ));

    $solution = $altcha->solveChallenge(new SolveChallengeOptions(
        algorithm: $algorithm,
        challenge: $challenge,
    ));

    $payload = [
        'challenge' => $challenge->toArray(),
        'solution' => [
            'counter' => $solution->counter,
            'derivedKey' => $solution->derivedKey,
        ],
    ];

    return [
        'encoded' => base64_encode(json_encode($payload)),
        'payload' => $payload,
    ];
}

/**
 * @param  callable(array<string, mixed>): array<string, mixed>  $mutator
 */
function tamperedAltchaPayload(callable $mutator, string $secret = 'test-secret'): string
{
    ['payload' => $payload] = altchaPayloadArray($secret);

    return base64_encode(json_encode($mutator($payload)));
}
