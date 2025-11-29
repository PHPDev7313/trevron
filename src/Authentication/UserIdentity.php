<?php

namespace JDS\Authentication;

use JDS\Contracts\Authentication\UserIdentityInterface;

class UserIdentity implements UserIdentityInterface
{
    public function __construct(
        public string $userId,
        public string $companyId,
        public array $roleIds,
        public array $permissionIds,
        public int $accessLevel,
        public bool $isAdmin,
        public string $email
    ) {}

    public function getUserId(): string { return $this->userId; }
    public function getCompanyId(): string { return $this->companyId; }
    public function getRoleIds(): array { return $this->roleIds; }
    public function getPermissionIds(): array { return $this->permissionIds; }
    public function getAccessLevel(): int { return $this->accessLevel; }
    public function isAdmin(): bool { return $this->isAdmin; }
    public function getEmail(): string { return $this->email; }
}

