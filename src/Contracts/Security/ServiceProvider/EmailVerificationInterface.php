<?php

namespace JDS\Contracts\Security\ServiceProvider;

use DomainException;

interface EmailVerificationInterface
{
    /**
     * requires TokenManagerInterface and a user repository with a
     *
     * method findByUserId(<string>)
     *
     * User repository also requires a
     *
     * method isEmailVerified(<string>) and markEmailVerified() in
     * the user Entity.
     *
     * This also requires a column in the user table of
     *
     * email_verified BOOLEAN NOT NULL DEFAULT false,
     *
     * email_verified_at datetime NULL DEFAULT NULL,
     *
     * @param string $token
     * @return void
     * @throws DomainException on invalid/expired token or missing user
     */
    public function verifyByToken(string $token): void;


}