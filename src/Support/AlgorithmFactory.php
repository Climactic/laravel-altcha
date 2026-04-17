<?php

declare(strict_types=1);

namespace Climactic\Altcha\Support;

use AltchaOrg\Altcha\Algorithm\Argon2id;
use AltchaOrg\Altcha\Algorithm\DeriveKeyInterface;
use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Algorithm\Scrypt;
use InvalidArgumentException;

final class AlgorithmFactory
{
    public static function make(?string $name = null): DeriveKeyInterface
    {
        return match (strtolower((string) ($name ?? 'pbkdf2'))) {
            'pbkdf2' => new Pbkdf2,
            'argon2id', 'argon2' => new Argon2id,
            'scrypt' => new Scrypt,
            default => throw new InvalidArgumentException("Unknown ALTCHA algorithm: {$name}"),
        };
    }
}
