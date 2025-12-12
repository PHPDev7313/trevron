<?php

namespace JDS\Configuration;

use JDS\Exceptions\Console\ConsoleRuntimeException;
use JDS\ServiceProvider\ConfigServiceProvider;
use League\Container\Container;

class FrameworkBootStrapper
{
    public static function boot(Container $container): void
    {
        //
        // Providers that MUST be fully registered EARLY
        //
        $coreProviders = [
            ConfigServiceProvider::class,
        ];
//        LoggingServiceProvider::class,
//        DatabaseServiceProvider::class,
//        DatabaseConsoleServiceProvider::class,
//        ConsoleServiceProvider::class,

        foreach ($coreProviders as $coreClass) {

            if (!$container->has($coreClass)) {
                //
                // provider was not added
                //
                throw new ConsoleRuntimeException("Core provider '{$coreClass}' was not registerd in services. Missing Core provider!");
            }

            //
            // Resolve provider instance
            //
            $provider = $container->get($coreClass);

            //
            // Force register() to run NOW
            //
            if (!method_exists($provider, 'register')) {
                throw new ConsoleRuntimeException("Core provider {$coreClass} was not registerd in services. Register method missing!");
            }

            $provider->register();

            //
            // Optional: support a "boot" lifecycle
            //
            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }
}

