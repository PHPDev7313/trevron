<?php

namespace JDS\Contracts\Security;

use JDS\Security\TokenPayload;

interface TokenManagerInterface
{
    /**
     * Generate a JWT token with a purpose and TTL
     *
     * @param string        $purpose        e.g. 'registration', 'password_reset'
     * @param int           $ttlSeconds     Lifetime in seconds
     * @param string|null   $userId
     * @param string|null   $email
     * @param array         $extraClaims    additional custom claims
     * @return string
     */
public function generateToken(
    string $purpose,
    int $ttlSeconds,
    ?string $userId = null,
    ?string $email = null,
    array $extraClaims = []
): string;

    /**
     * Validate the token and return a TokenPayLoad or null if invalid/expired.
     *
     * @param string                $token
     * @param string|null           $expectedPurpose if set, token must match this purpose
     * @return TokenPayload|null
     */
public function validateToken(string $token, ?string $expectedPurpose = null): ?TokenPayload;

}

