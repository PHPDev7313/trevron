<?php

namespace JDS\Contracts\Authentication;

interface AuthRoleInterface
{

    // this is the role_id
    public function getRole(): string;

}

