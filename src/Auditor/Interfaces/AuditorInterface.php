<?php

namespace JDS\Auditor\Interfaces;

use JDS\Auditor\CentralizedLogger;
use Psr\Log\LoggerInterface;

interface AuditorInterface
{
    public function registerLogger(string $name, LoggerInterface $logger): void;

    public function getLogger(string $name): CentralizedLogger;

}

