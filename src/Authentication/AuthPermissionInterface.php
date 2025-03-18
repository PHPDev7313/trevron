<?php

namespace JDS\Authentication;

interface AuthPermissionInterface
{
    // this is the permission_id
    public function getPermission(): string;

}

