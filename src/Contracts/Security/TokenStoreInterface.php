<?php

namespace JDS\Contracts\Security;

interface TokenStoreInterface
{
    public function store(string $tokenId, int $expiredAt): void;

    public function isUsed(string $tokenId): bool;

    public function markUsed(string $tokenId): void;

    public function purgeExpired(): int;
}

