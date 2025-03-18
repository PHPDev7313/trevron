<?php

namespace JDS\Auditor;

use JDS\Auditor\Exception\LoggerNotFoundException;
use JDS\Auditor\Interfaces\AuditorInterface;
use Psr\Log\LoggerInterface;


class LoggerManager implements AuditorInterface
{
    private array $loggers = [];

    /**
     * Register a Monolog logger under a unique name.
     *
     * @param string $name            Logger name (e.g., 'audit', 'errors')
     * @param \Psr\Log\LoggerInterface $logger Monolog logger instance
     */
    public function registerLogger(string $name, LoggerInterface $logger): void
    {
        $this->loggers[$name] = new CentralizedLogger($logger);
    }

    /**
     * Retrieve a registered CentralizedLogger by name.
     *
     * @param string $name Logger name (e.g., 'audit', 'errors')
     * @return CentralizedLogger
     * @throws LoggerNotFoundException If the logger is not registered
     */
    public function getLogger(string $name): CentralizedLogger
    {
        if (!isset($this->loggers[$name])) {
            throw new LoggerNotFoundException("Logger '{$name}' is not registered.");
        }

        return $this->loggers[$name];
    }
}


