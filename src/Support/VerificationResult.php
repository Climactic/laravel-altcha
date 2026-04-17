<?php

declare(strict_types=1);

namespace Climactic\Altcha\Support;

final class VerificationResult
{
    private function __construct(
        public readonly bool $verified,
        public readonly ?string $reason = null,
        public readonly ?string $replayKey = null,
    ) {}

    public static function ok(?string $replayKey = null): self
    {
        return new self(true, null, $replayKey);
    }

    public static function failed(string $reason): self
    {
        return new self(false, $reason);
    }
}
