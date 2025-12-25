<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Security\SecretsConfigInterface;
use JDS\Contracts\Security\SecretsInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Exceptions\CryptoRuntimeException;
use JDS\Security\Secrets;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;
use JDS\Security\SecretsValidator;
use League\Container\Container;

final class SecretsServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->addShared(SecretsInterface::class, function () use ($container) {

            /** @var SecretsConfigInterface $config */
            $config = $container->get(SecretsConfigInterface::class);

            $this->assertSodiumLoaded();

            $crypto = $this->makeCrypto($config->appKeyBase64());
            $manager = $this->makeManager($config->secretsFile(), $crypto);

            $secretsArray = $manager->load();

            $schema = $this->loadSchema($config->schemaFile());
            $validator = $this->makeValidator($schema);

            $validator->validate($secretsArray);

            return new Secrets($secretsArray);
         });

        // Aliases
        $container->addShared(
            Secrets::class,
            fn () => $container->get(SecretsInterface::class)
        );
        $container->addShared(
            'secrets',
            fn () => $container->get(SecretsInterface::class)
        );
    }

    // ----- Test seams (protected, produciton safe) -----

    protected function assertSodiumLoaded(): void
    {
        if (!extension_loaded('sodium')) {
            throw new CryptoRuntimeException(
                'The sodium extension is required for secrets encryption/decryption.'
            );
        }
    }

    protected function makeCrypto(string $appSecretKeyBase64): SecretsCrypto
    {
        return SecretsCrypto::fromBase64($appSecretKeyBase64);
    }

    protected function makeManager(string $secretsFilePath, SecretsCrypto $crypto): SecretsManager
    {
        return new SecretsManager($secretsFilePath, $crypto);
    }

    protected function loadSchema(string $schemaFilePath): array
    {
        $schema = json_decode(file_get_contents($schemaFilePath), true);

        if (!is_array($schema)) {
            $filename = basename($schemaFilePath);
            throw new CryptoRuntimeException("Invalid schema file {filename} - MUST be valid JSON.");
        }

        return $schema;
    }

    protected function makeValidator(array $schema): SecretsValidator
    {
        return new SecretsValidator($schema);
    }
}

