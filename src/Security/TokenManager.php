<?php

namespace JDS\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JDS\Contracts\Security\TokenManagerInterface;

class TokenManager implements TokenManagerInterface
{
    public function __construct(
        private string $secretKey,
        private string $algo = 'HS256'
    ) {}

    /**
     * @inheritDoc
     */
    public function generateToken(
        string $purpose,
        int $ttlSeconds,
        ?string $userId = null,
        ?string $email = null,
        array $extraClaims = []): string
    {
        if ($ttlSeconds <= 0) {
            throw new \InvalidArgumentException("TTL must be a positive integer.");
        }

        $now = time();

        $baseClaims = [
            'purpose' => $purpose,
            'expires' => $now + $ttlSeconds,
        ];

        if ($userId !== null) {
            $baseClaims['user_id'] = $userId;
        }

        if ($email !== null) {
            $baseClaims['email'] = $email;
        }

        $claims = array_merge($baseClaims, $extraClaims);

        return JWT::encode($claims, $this->secretKey, $this->algo);
    }

    /**
     * @inheritDoc
     */
    public function validateToken(string $token, ?string $expectedPurpose = null): ?TokenPayload
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algo));
            $claims = (array) $decoded;

            $purpose = $claims['purpose'] ?? null;
            $expires = $claims['expires'] ?? null;

            if ($purpose === null || $expires === null) {
                return null;
            }

            $payload = new TokenPayload(
                purpose: $purpose,
                userId: $claims['user_id'] ?? null,
                email: $claims['email'] ?? null,
                expires: (int) $expires,
                claims: $claims
            );

            if ($payload->isExpired()) {
                return null;
            }

            if ($expectedPurpose !== null && $payload->purpose !== $expectedPurpose) {
                return null;
            }
            return $payload;
        } catch (\Throwable $e) {
            // Any decoding / signature / format issue => invalid token
            return null;
        }
    }
}