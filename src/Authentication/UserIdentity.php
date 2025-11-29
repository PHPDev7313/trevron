<?php

namespace JDS\Authentication;

class UserIdentity
{
    public function __construct(
        public readonly string $userId,
        public readonly string $companyId,
        public readonly array $roleIds,
        public readonly array $permissionIds,
        public readonly int $bitmask,
        public readonly bool $isAdmin,
        public readonly string $email
    )
    {
    }
}