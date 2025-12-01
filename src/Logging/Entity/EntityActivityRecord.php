<?php

namespace JDS\Logging\Entity;

use DateTimeImmutable;

class EntityActivityRecord
{
    public function __construct(
        public string $log_id,
        public readonly string $entityName,
        public readonly string $entityId,
        public readonly string $action,
        public readonly array $fields,
        public readonly ?string $userId,
        public readonly DateTimeImmutable $timestamp,
    ) {}
}

