<?php

namespace JDS\ServiceProvider;

use JDS\Configuration\Config;
use JDS\Console\Command\Secrets\DecryptSecretsCommand;
use JDS\Console\Command\Secrets\EditSecretsCommand;
use JDS\Console\Command\Secrets\EncryptSecretsCommand;
use JDS\Console\Command\Secrets\ValidateSecretCommand;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Exceptions\Configuration\ConfigRuntimeException;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class ConfigSecretsServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        //
        // 🔐 IRON-CLAD SAFETY CHECK
        // Load config first
        // Ensure the Config service exists before proceeding.
        //
        if (!$container->has(Config::class)) {
            throw new ConfigRuntimeException(
                "Configuration data must be loaded first through the Configuration class. [Config:Secrets:Service:Provider]."
            );
        }

        $config = $container->get(Config::class);

        //
        // Ensure basePath exists
        //
        $basePath = $config->get('basePath');
        if (!is_string($basePath) || trim($basePath) === '') {
            throw new ConfigRuntimeException(
                "Missing or invalid configuration parameter 'basePath' from Configuration class. [Config:Secrets:Service:Provider]."
            );
        }

        //
        // Load secrets config (may be null)
        //
        $secretFile = $config->get('secrets.file');
        $plainFile = $config->get('secrets.plain');
        $schemaFile = $config->get('secrets.schema');
        $appKey = $config->get('app.secret.key');

        //
        // Critical requirement: app.secret.key must exist and be valid.
        //
        if (!is_string($appKey) || trim($appKey) === '') {
            throw new ConfigRuntimeException(
                "Missing or invalid 'app.secret.key' from Configuration class. [Config:Secrets:Service:Provider]."
            );
        }

        //
        // Resolve paths relative to basePath (with defaults)
        //
        $encryptedPath = $this->resolvePath(
            $basePath,
            $secretFile,
            'config/secrets.json.enc'
        );

        $plainPath = $this->resolvePath(
            $basePath,
            $plainFile,
            'config/secrets.plain.json'
        );

        $schemaPath = $this->resolvePath(
            $basePath,
            $schemaFile,
            'config/secrets.schema.json'
        );

        //
        // Register console commands
        //
        $container->add(EncryptSecretsCommand::class)
            ->addArguments([
                new StringArgument($appKey),
                new StringArgument($plainPath),
                new StringArgument($encryptedPath),
            ])->setShared(true);

        $container->add(DecryptSecretsCommand::class)
            ->addArguments([
                new StringArgument($appKey),
                new StringArgument($encryptedPath),
            ])->setShared(true);

        $container->add(ValidateSecretCommand::class)
            ->addArguments([
                new StringArgument($schemaPath),
                new StringArgument($plainPath),
            ])->setShared(true);

        $container->add(EditSecretsCommand::class)
            ->addArguments([
                new StringArgument($appKey),
                new StringArgument($plainPath),
                new StringArgument($encryptedPath),
            ])->setShared(true);
    }

    private function resolvePath(string $basePath, ?string $relative, string $default): string
    {
        //
        // If path is null or empty, fallback to default
        //
        $file = $relative ?: $default;

        // Guarntee formatting
        return rtrim($basePath, '/') . '/' . ltrim($file, '/');
    }
}

