<?php

namespace JDS\Contracts\Authentication;

interface AuthPermissionInterface
{
    // this is the permission_id
    public function getPermission(): string;

}

