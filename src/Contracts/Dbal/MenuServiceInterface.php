<?php

namespace JDS\Contracts\Dbal;

use JDS\Dbal\Entity;

interface MenuServiceInterface extends ServiceInterface
{
    public function getWithMenus(string $id): ?Entity;
}

