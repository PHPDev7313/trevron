<?php

namespace JDS\Bootstrap;

use JDS\Configuration\Config;
use JDS\Handlers\ExceptionHandler;
use JDS\Logging\ExceptionLogger;
use JDS\Processing\ErrorProcessor;
use League\Container\Container;

class AppBootstrapper
{
    /**
     * Bootstraps the full application enviornment after providers are registered.
     */
    public static function boot(Container $container): void
    {
        //
        // 1. Provider Boot Phase (optional Lifecyle)
        //
        self::bootProvider($container);

        //
        // 2. Global Exception + Error Processor Initialization
        //
        self::initializeGlobalHandler($container);
    }

    private static function bootProvider(Container $container): void
    {
        if (!$container->has('providers.boot')) {
            return;
        }

        $providerList = $container->get('providers.boot');

        foreach ($providerList as $providerClass) {
            if (!$container->has($providerClass)) {
                continue;
            }

            $provider = $container->get($providerClass);

//            if (method_exists($provider, 'register')) {
//                $provider->register();
//            }

            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }

    private static function initializeGlobalHandler(Container $container): void
    {
        //
        // Ensure config exists
        //
        if (!$container->has(Config::class)) {
            return;
        }

        $config = $container->get(Config::class);

        //
        // 1. Initialize core exception handler
        //
        if ($container->has(ExceptionHandler::class)) {
            ExceptionHandler::initializeWithEnvironment(
                $config->getEnvironment()
            );
        }

        //
        // 2. Initialize the global error processor
        //
        if ($container->has(ErrorProcessor::class)) {
            $exceptionLogger = $container->get(ExceptionLogger::class);
            ErrorProcessor::initialize($exceptionLogger);
        }
    }
}

