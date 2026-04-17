<?php

declare(strict_types=1);

namespace Climactic\Altcha\Support;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\ChallengeParameters;
use AltchaOrg\Altcha\Payload;
use AltchaOrg\Altcha\ServerSignature;
use AltchaOrg\Altcha\Solution;
use AltchaOrg\Altcha\VerifySolutionOptions;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as Config;

final class Verifier
{
    public const REASON_MISSING = 'missing';

    public const REASON_MALFORMED = 'malformed';

    public const REASON_INVALID = 'invalid';

    public const REASON_EXPIRED = 'expired';

    public const REASON_REPLAY = 'replay';

    public function __construct(
        private readonly Config $config,
        private readonly CacheFactory $cache,
    ) {}

    public function verify(?string $rawPayload): VerificationResult
    {
        if ($rawPayload === null || $rawPayload === '') {
            return VerificationResult::failed(self::REASON_MISSING);
        }

        $decoded = base64_decode($rawPayload, true);
        if ($decoded === false) {
            return VerificationResult::failed(self::REASON_MALFORMED);
        }

        $payload = json_decode($decoded, true);
        if (! is_array($payload)) {
            return VerificationResult::failed(self::REASON_MALFORMED);
        }

        $hmacSecret = (string) $this->config->get('altcha.hmac_secret', '');

        if (isset($payload['verificationData'])) {
            $result = ServerSignature::verifyServerSignature($payload, $hmacSecret);

            if ($result->expired) {
                return VerificationResult::failed(self::REASON_EXPIRED);
            }

            return $result->verified
                ? VerificationResult::ok($this->replayKey($payload))
                : VerificationResult::failed(self::REASON_INVALID);
        }

        if (! isset($payload['challenge'], $payload['solution']) ||
            ! is_array($payload['challenge']) || ! is_array($payload['solution'])) {
            return VerificationResult::failed(self::REASON_MALFORMED);
        }

        $challenge = new Challenge(
            ChallengeParameters::fromArray($payload['challenge']['parameters'] ?? []),
            $payload['challenge']['signature'] ?? null,
        );

        $solution = new Solution(
            counter: (int) ($payload['solution']['counter'] ?? 0),
            derivedKey: (string) ($payload['solution']['derivedKey'] ?? ''),
        );

        $altcha = new Altcha(
            hmacSignatureSecret: $hmacSecret,
            hmacKeySignatureSecret: $this->config->get('altcha.hmac_key_secret'),
        );

        $result = $altcha->verifySolution(new VerifySolutionOptions(
            algorithm: AlgorithmFactory::make($this->config->get('altcha.algorithm')),
            payload: new Payload($challenge, $solution),
        ));

        if ($result->expired) {
            return VerificationResult::failed(self::REASON_EXPIRED);
        }

        if (! $result->verified) {
            return VerificationResult::failed(self::REASON_INVALID);
        }

        $replayKey = $challenge->signature ?? $solution->derivedKey;

        if ($this->isReplay($replayKey)) {
            return VerificationResult::failed(self::REASON_REPLAY);
        }

        $this->markUsed($replayKey);

        return VerificationResult::ok($replayKey);
    }

    private function replayKey(array $payload): string
    {
        return (string) ($payload['signature'] ?? ($payload['verificationData'] ?? ''));
    }

    private function isReplay(string $key): bool
    {
        if ($key === '') {
            return false;
        }

        return $this->cacheStore()->has($this->cacheKey($key));
    }

    private function markUsed(string $key): void
    {
        if ($key === '') {
            return;
        }

        $this->cacheStore()->put(
            $this->cacheKey($key),
            true,
            (int) $this->config->get('altcha.expires', 300),
        );
    }

    private function cacheKey(string $key): string
    {
        return 'altcha:used:'.hash('sha256', $key);
    }

    private function cacheStore(): CacheRepository
    {
        return $this->cache->store($this->config->get('altcha.cache_store'));
    }
}
