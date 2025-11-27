<?php

namespace JDS\Auth\Event;

use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

class UserLoggedIn extends Event
{
    public function __construct(
        public readonly string $userId,
        public readonly ?string $companyId,
        public readonly array $permissions,
        public readonly DateTimeImmutable $when
    )
    {
    }
}