<?php

namespace JDS\Contracts\Auditor;

interface ActivityLoggerInterface
{
    /**
     * Persist an audit Log entry for a changed entity.
     *
     * @param string     $entityType
     * @param string|int $entityId
     * @param array      $changes
     *
     * @return void
     */
    public function log(string $entityType, string|int $entityId, array $changes): void;

}

