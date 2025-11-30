<?php

namespace JDS\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JDS\Contracts\Security\TokenManagerInterface;
use Throwable;

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
        $tokenId = bin2hex(random_bytes(16));
        $issuedAt = time();
        $expiresAt = $issuedAt + $ttlSeconds;

        $payload = array_merge([
            'token_id' => $tokenId,
            'purpose' => $purpose,
            'user_id' => $userId,
            'email' => $email,
            'iat' => $issuedAt,
            'exp' => $expiresAt
        ], $extraClaims);

        //
        // 1) Store takenId for single-use
        $this->tokenStore->store($tokenId, $expiresAt);

        return JWT::encode($payload, $this->secretKey, 'HS256');

//        $now = time();
//
//        $baseClaims = [
//            'purpose' => $purpose,
//            'expires' => $now + $ttlSeconds,
//        ];
//
//        if ($userId !== null) {
//            $baseClaims['user_id'] = $userId;
//        }
//
//        if ($email !== null) {
//            $baseClaims['email'] = $email;
//        }
//
//        $claims = array_merge($baseClaims, $extraClaims);
//
//        return JWT::encode($claims, $this->secretKey, $this->algo);
    }

    /**
     * @inheritDoc
     */
    public function validateToken(string $token, ?string $expectedPurpose = null): ?TokenPayload
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algo));
        } catch (Throwable $e) {
            return null;
        }

        if (($decoded['purpose'] ?? null) !== $expectedPurpose) {
            return null;
        }

        $tokenId = $decoded['token_id'] ?? null;
        if (!$tokenId) {
            return null;
        }

        //
        // 2) Check single-use status
        //
        if ($this->tokenStore-isUsed($tokenId)) {
            return null; // Already used! Rejected.
        }

        //
        // 3) Mark as used
        //
        $this->tokenStore->markUsed($tokenId);


//            $claims = (array) $decoded;
//
//            $purpose = $claims['purpose'] ?? null;
//            $expires = $claims['expires'] ?? null;
//
//            if ($purpose === null || $expires === null) {
//                return null;
//            }



            return new TokenPayload(
                tokenId: $tokenId,
                purpose: $decoded['purpose'],
                userId: $decoded['user_id'] ?? null,
                email: $decoded['email'] ?? null,
                expires: $decoded['exp']
            );

//            if ($payload->isExpired()) {
//                return null;
//            }
//
//            if ($expectedPurpose !== null && $payload->purpose !== $expectedPurpose) {
//                return null;
//            }
//            return $payload;
//        } catch (\Throwable $e) {
//            // Any decoding / signature / format issue => invalid token
//            return null;
//        }
    }
}

