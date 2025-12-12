<?php

namespace JDS\Auditor;

use JDS\Contracts\Auditor\AuditorInterface;
use JDS\Exceptions\Auditor\LoggerNotFoundException;
use Psr\Log\LoggerInterface;


class LoggerManager implements AuditorInterface
{
    private array $loggers = [];

    private string $name;
    /**
     * Register a Monolog logger under a unique name.
     *
     * @param string $name            Logger name (e.g., 'audit', 'errors')
     * @param \Psr\Log\LoggerInterface $logger Monolog logger instance
     */
    public function registerLogger(string $name, LoggerInterface $logger): void
    {
        $this->loggers[$name] = new CentralizedLogger($logger);
        $this->name = $name;
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

    public function getName(): string
    {
        return $this->name;
    }
}


