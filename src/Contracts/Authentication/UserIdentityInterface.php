<?php

namespace JDS\Contracts\Authentication;

interface UserIdentityInterface
{
    public function getUserId(): string;
    public function getCompanyId(): string;
    public function getRoleIds(): array;
    public function getPermissionIds(): array;
    public function getAccessLevel(): int;
    public function isAdmin(): bool;
    public function getEmail(): string;
}