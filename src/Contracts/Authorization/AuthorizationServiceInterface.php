<?php

namespace JDS\Contracts\Authorization;

use JDS\Contracts\Authentication\UserIdentityInterface;

interface AuthorizationServiceInterface
{
    public function getIdentity(): ?UserIdentityInterface;
    public function allows(int $requiredLevel): bool;
    public function requires(int $requiredLevel): void;
    public function isAdmin(): bool;
}

