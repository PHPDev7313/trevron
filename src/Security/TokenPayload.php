<?php

namespace JDS\Security;

class TokenPayload
{
    public function __construct(
        public string $tokenId,
        public string $purpose,
        public ?string $userId,
        public ?string $email,
        public int $expires
    ) {}

    public function isExpired(): bool
    {
        return time() > $this->expires;
    }
}

