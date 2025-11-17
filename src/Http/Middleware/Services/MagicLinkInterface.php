<?php

namespace JDS\Http\Middleware\Services;
use \Doctrine\DBAL\Connection;
use JDS\Http\InvalidArgumentException;

/**
 * MagicLinkTokenInterface
 *
 * Responsible for creating, storing, sending, and validating one-time magic links.
 * Magic link tokens always begin with 'jds'.
 */
interface MagicLinkInterface
{

    /**
     * private \Doctrine\DBAL\Connection - required
     * private string $baseUrl - required
     * private int $defaultTtl (seconds) - required
     *
     *
     * __construct(private Connection $connection, private string $baseUrl, private int $defaultTtl=900)
     *
     *
     * Create and persist a magic link record and return the public URL parts.
     *
     * @param string $userId - user id to sign in as
     * @param int|null $ttlSeconds - optional TTL override in seconds
     * @param string|null $purpose - optional purpose string (login, invite, onboarding)
     * @param string|null $redirectUrl - optional post-login redirect URL
     * @param array $options - optional extra metadata ['ip' => '1.2.3.4', 'user_agent' => 'UA string']
     *
     * @return array { 'url' => string, 'magiclink_id_hex' => string, 'token' => string }
     *          NOTE: token is returned so you can email/send it. Do NOT store raw token in DB.
     *
     */
    public function createLink(string $userId, ?int $ttlSeconds=null, ?string $purpose="login", ?string $redirectUrl=null, array $options=[]): array;

    /**
     * Validate token against a magiclink_id (hex string) and optionally create session.
     *
     * Returns an array on success containing the record and actor (user_id) or throws on failer.
     *
     * @param string $magiclinkIdHex
     * @param string $tokenRaw
     * @param array $options Optional checks ['ip' => '1.2.3.4', 'user_agent' => 'UA string', 'consume' => true]
     *
     * @return array ['user_id' => mixed, 'record' => array]
     * @throws InvalidArgumentException on invalid input or token
     */
    public function validateToken(string $magiclinkIdHex, string $tokenRaw, array $options=[]): array;

    /**
     * Optional helper to build a user-facing link.
     *
     * @param string $magiclinkIdHex
     * @param string $tokenRaw
     * @return string
     */
    public function buildUrl(string $magiclinkIdHex, string $tokenRaw): string;

    /**
     * Revoke (invalidate) a magic link by id (mark used_at).
     * @param string $magiclinkIdHex
     * @return void
     */
    public function revoke(string $magiclinkIdHex): void;

}