<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2026 Jessop Digital Systems
 * Date: January 5, 2026
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 *    : ConsoleBootstrapLifecycle.v1.2.2.FINAL.md
 */

namespace JDS\ServiceProvider;

use JDS\Auditor\LoggerManager;
use JDS\Configuration\Config;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Exceptions\Configuration\ConfigRuntimeException;
use JDS\Exceptions\Loggers\LoggerRuntimeException;
use JDS\Handlers\ExceptionHandler;
use JDS\Http\StatusCodeManager;
use JDS\Logging\ActivityLogger;
use JDS\Logging\ExceptionLogger;
use JDS\Processing\ErrorProcessor;
use League\Container\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggingServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        //
        // Iron-clad: Config MUST exist
        //
        if (!$container->has(Config::class)) {
            throw new ConfigRuntimeException(
                "Configuration class must be registered before LoggingServiceProvider runs. [Logging:Service:Provider]."
            );
        }

        /** @var Config $config */
        $config = $container->get(Config::class);

        $loggerConfig = $config->get("logging.loggers");

        if (!is_array($loggerConfig) || empty($loggerConfig)) {
            throw new LoggerRuntimeException(
                "Missing or invalid 'loggers' config. [Logging:Service:Provider]."
            );
        }

        if (!isset($loggerConfig['basic'], $loggerConfig['exception'])) {
            throw new ConfigRuntimeException(
                "Logging configuration must define 'basic' and 'exception' logger entries. [Logging:Service:Provider]."
            );
        }

        if (!$container->has(StatusCodeManager::class)) {
            $container->addShared(StatusCodeManager::class);
        }

        $basicCfg = $loggerConfig["basic"];
        $exceptionCfg = $loggerConfig["exception"];

        $this->assertLoggerConfig('basic', $basicCfg);
        $this->assertLoggerConfig('exception', $exceptionCfg);

        //
        // Build Monolog loggers (no storing raw arrays in the container)
        //
        $basicLogger = $this->buildMonologLogger($basicCfg);
        $exceptionLogger = $this->buildMonologLogger($exceptionCfg);

        //
        // Activitylogger (wraps basic logger)
        //
        $container->addShared(ActivityLogger::class)
            ->addArgument($basicLogger);

        //
        // ExceptionLogger (wraps exception logger)
        //
        $container->addShared(ExceptionLogger::class)
            ->addArguments([
                $exceptionLogger,
                StatusCodeManager::class,
                $config->isProduction(),
            ]);

        //
        // LoggerManager - registry of loggers (services, not data)
        //
        $manager = new LoggerManager();
        $manager->registerLogger('basic', $basicLogger);
        $manager->registerLogger('exception', $exceptionLogger);

        $container->addShared(LoggerManager::class, $manager);

        //
        // Global error / exception writing (static, not container state)
        //
        if ($container->has(ExceptionHandler::class)) {
            ExceptionHandler::initializeWithEnvironment(
                $config->getEnvironment()
            );
        }

        ErrorProcessor::initialize(
            $container->get(ExceptionLogger::class)
        );
    }

    /**
     * @param array<string, mixed> $cfg
     */
    private function assertLoggerConfig(string $key, array $cfg): void
    {
        foreach (['name', 'path', 'level'] as $required) {
            if (!array_key_exists($required, $cfg) || !is_string($cfg[$required]) || trim($cfg[$required]) === '') {
                throw new ConfigRuntimeException(
                    "Logger '{$key}' is missing or has invalid '{$required}' configuration. [Logging:Service:Provider]."
                );
            }
        }
    }

    /**
     * @param array<string, string> $cfg
     */
    private function buildMonologLogger(array $cfg): Logger
    {
        $logger = new Logger($cfg['name']);
        $handler = new StreamHandler(
            $cfg['path'],
            Logger::toMonologLevel($cfg['level'])
        );
        $logger->pushHandler($handler);

        return $logger;
    }
}

