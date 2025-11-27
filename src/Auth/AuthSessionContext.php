<?php

namespace JDS\Auth;

use JDS\Contracts\Auth\ContextInterface;

class AuthSessionContext implements ContextInterface
{

    public string $user_id;
    public string $company_id;
    public ?string $role_id = null;
    public array $permissions = [];
    public bool $is_admin = false;
    public int $access_level = 99;
    public array $meta = [];


    /**
     * @inheritDoc
     */
    public function hasPermission(string $permisison): bool
    {
        if ($this->is_admin) {
            return true;
        }
        return in_array($permisison, $this->permissions, true);
    }

    /**
     * @inheritDoc
     */
    public function isAtLeast(int $level): bool
    {
        return $this->access_level <= $level;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'role_id' => $this->role_id,
            'is_admin' => $this->is_admin,
            'access_level' => $this->access_level,
            'permissions' => $this->permissions,
            'meta' => $this->meta,
        ];
    }
}

