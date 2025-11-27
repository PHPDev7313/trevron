<?php

namespace JDS\Contracts\Auth;

use JDS\Auth\AuthSessionContext;

interface AuthServiceInterface
{
    /**
     * Authenticate user by email/password and build session context.
     *
     * Returns AuthSessionContext on success or null on failure.
     *
     * @param string $email
     * @param string $password
     * @param string|null $company_id
     * @return ContextInterface|null
     */
    public function login(string $email, string $password, ?string $companyId = null): bool;

}

