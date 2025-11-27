<?php

namespace JDS\Contracts\Auth;

interface ResolverInterface
{

    /**
     * Resolve permissions for a user in a company.
     *
     * Returns array of permission *names* (strings).
     * @param string $companyId
     * @param string $userId
     * @param string|null $roleId
     * @return array
     */
    public function resolvePermissions(string $companyId, string $userId, ?string $roleId=null): array;

}

