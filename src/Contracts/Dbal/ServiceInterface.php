<?php

namespace JDS\Contracts\Dbal;

use JDS\Dbal\Entity;

interface ServiceInterface
{
    public function lookup(string $id): ?Entity;

    public function delete(Entity $entity): bool;
}


