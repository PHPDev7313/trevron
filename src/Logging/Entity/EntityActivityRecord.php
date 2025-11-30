<?php

namespace JDS\Logging\Entity;

class EntityActivityRecord
{
    public function __construct(
        public readonly string $entityName,
        public readonly string $entityId,
        public readonly string $action,
        public readonly array $fields,
        public readonly ?string $userId,
        public readonly \DateTimeImmutable $timestamp,
    ) {}
}

