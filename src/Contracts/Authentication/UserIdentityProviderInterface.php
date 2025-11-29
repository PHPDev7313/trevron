<?php

namespace JDS\Contracts\Authentication;

interface UserIdentityProviderInterface
{
    /**
     * Build a complete authorization identity for a user.
     *
     * The implementing class MUST:
     * - Resolve company_id from pivot tables
     * - Resolve role_id from company context
     * - Resolve permission_ids assigned directly to the user
     * - Resolve combined access_level OR value from permissions
     * - Return an object implementing UserIdentityInterface
     */
    public function buildIdentity(AuthUserInterface $user): UserIdentityInterface;
}