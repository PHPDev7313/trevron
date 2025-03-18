<?php

namespace JDS\Auditor\Interfaces;

interface LogHandlerInterface
{
    public function handle(array $logEntry): void;

    public function readLog(?string $level=null, ?string $startDate=null, ?string $endDate=null, int $limit=100): array;
}

