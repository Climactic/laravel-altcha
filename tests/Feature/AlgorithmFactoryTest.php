<?php

declare(strict_types=1);

use AltchaOrg\Altcha\Algorithm\Argon2id;
use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Algorithm\Scrypt;
use Climactic\Altcha\Support\AlgorithmFactory;

it('defaults to pbkdf2', function (): void {
    expect(AlgorithmFactory::make())->toBeInstanceOf(Pbkdf2::class)
        ->and(AlgorithmFactory::make(null))->toBeInstanceOf(Pbkdf2::class)
        ->and(AlgorithmFactory::make('pbkdf2'))->toBeInstanceOf(Pbkdf2::class);
});

it('accepts argon2 and argon2id as aliases', function (): void {
    expect(AlgorithmFactory::make('argon2'))->toBeInstanceOf(Argon2id::class)
        ->and(AlgorithmFactory::make('argon2id'))->toBeInstanceOf(Argon2id::class)
        ->and(AlgorithmFactory::make('ARGON2ID'))->toBeInstanceOf(Argon2id::class);
});

it('returns a Scrypt instance for scrypt', function (): void {
    expect(AlgorithmFactory::make('scrypt'))->toBeInstanceOf(Scrypt::class);
});

it('throws on an unknown algorithm name', function (): void {
    AlgorithmFactory::make('md5');
})->throws(InvalidArgumentException::class, 'Unknown ALTCHA algorithm: md5');
