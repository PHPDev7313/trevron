<?php

namespace JDS\Logging\Entity;

use DateTimeImmutable;

readonly class EntityLogEntry
{
    public function __construct(
        public int               $id,
        public string            $logId,
        public string            $entity_name,
        public string            $entityId,
        public string            $action,
        public string            $fields,
        public ?string           $userId,
        public DateTimeImmutable $timestamp,
    ) {}
}

