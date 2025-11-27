<?php

namespace JDS\Contracts\Auth;

interface ContextInterface
{

    /**
     * Check if user has permission
     *
     * @param string $permisison
     * @return bool
     */
    public function hasPermission(string $permisison): bool;

    /**
     * Check a minimal access-level threshold.
     *
     * e.g. isAtLeast(2) returns true if access_level <= 2
     *
     * @param int $level
     * @return bool
     */
    public function isAtLeast(int $level): bool;

    /**
     * Helpful for debugging, returns a serializable array.
     *
     * @return array
     */
    public function toArray(): array;
}

