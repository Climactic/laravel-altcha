<?php

declare(strict_types=1);

namespace Climactic\Altcha\Http\Middleware;

use Climactic\Altcha\Support\VerificationResult;
use Climactic\Altcha\Support\Verifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class VerifyAltcha
{
    public function __construct(private readonly Verifier $verifier) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('altcha.enabled')) {
            return $next($request);
        }

        if (! $request->isMethod('POST')) {
            return $next($request);
        }

        if ($request->user()) {
            return $next($request);
        }

        $field = (string) config('altcha.field', 'altcha');
        $result = $this->verifier->verify($request->input($field));

        if (! $result->verified) {
            throw ValidationException::withMessages([
                $field => $this->messageFor($result),
            ]);
        }

        return $next($request);
    }

    private function messageFor(VerificationResult $result): string
    {
        return match ($result->reason) {
            Verifier::REASON_MISSING => __('Please complete the security challenge.'),
            Verifier::REASON_REPLAY => __('Security challenge has already been used. Please try again.'),
            Verifier::REASON_EXPIRED => __('Security challenge expired. Please try again.'),
            default => __('Security challenge verification failed. Please try again.'),
        };
    }
}
