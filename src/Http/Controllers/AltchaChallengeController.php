<?php

declare(strict_types=1);

namespace Climactic\Altcha\Http\Controllers;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\CreateChallengeOptions;
use Climactic\Altcha\Support\AlgorithmFactory;
use Illuminate\Http\JsonResponse;

final class AltchaChallengeController
{
    public function __invoke(): JsonResponse
    {
        if (! config('altcha.enabled')) {
            abort(404);
        }

        $altcha = new Altcha(
            hmacSignatureSecret: (string) config('altcha.hmac_secret'),
            hmacKeySignatureSecret: config('altcha.hmac_key_secret'),
        );

        $challenge = $altcha->createChallenge(new CreateChallengeOptions(
            algorithm: AlgorithmFactory::make(config('altcha.algorithm')),
            cost: (int) config('altcha.cost', 10000),
            expiresAt: time() + (int) config('altcha.expires', 300),
            memoryCost: config('altcha.memory_cost'),
            parallelism: config('altcha.parallelism'),
        ));

        return new JsonResponse($challenge->toArray());
    }
}
