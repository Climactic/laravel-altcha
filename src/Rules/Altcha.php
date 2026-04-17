<?php

declare(strict_types=1);

namespace Climactic\Altcha\Rules;

use Climactic\Altcha\Support\Verifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class Altcha implements ValidationRule
{
    public bool $implicit = true;

    public function __construct(private readonly ?Verifier $verifier = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! config('altcha.enabled')) {
            return;
        }

        $verifier = $this->verifier ?? app(Verifier::class);
        $result = $verifier->verify(is_string($value) ? $value : null);

        if ($result->verified) {
            return;
        }

        $fail(match ($result->reason) {
            Verifier::REASON_MISSING => __('Please complete the security challenge.'),
            Verifier::REASON_REPLAY => __('Security challenge has already been used. Please try again.'),
            Verifier::REASON_EXPIRED => __('Security challenge expired. Please try again.'),
            default => __('Security challenge verification failed. Please try again.'),
        });
    }
}
