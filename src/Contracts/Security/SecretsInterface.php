<?php

namespace JDS\Contracts\Security;

interface SecretsInterface
{
    /**
     * Get a secret by dot-notated path, e.g. "db.user", "jwt.access".
     */
    public function get(string $path, mixed $default = null): mixed;

    /**
     * Get all secrets as an array (use sparingly).
     */
    public function all(): array;

    public function has(string $path): bool;

}

