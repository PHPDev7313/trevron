<?php

namespace JDS\Security;

class TokenPayload
{
    public function __construct(
        public string $purpose,
        public ?string $userId,
        public ?string $email,
        public int $expires,
        public array $claims = [],
    ) {}

    public function isExpired(): bool
    {
        return time() > $this->expires;
    }
}

