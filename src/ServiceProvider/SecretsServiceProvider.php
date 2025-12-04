<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Security\SecretsInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Exceptions\CryptoRuntimeException;
use JDS\Security\Secrets;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;
use League\Container\Container;

class SecretsServiceProvider implements ServiceProviderInterface
{

    protected array $provides = [
        SecretsInterface::class,
        Secrets::class,
        'secrets',
    ];

    public function __construct(
        private readonly Container $container,
        private readonly string $secretsFilePath,
        private readonly string $appSecretKeyBase64
    )
    {
    }

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $this->container->addShared(SecretsInterface::class, function () {
            if (!extension_loaded('sodium')) {
                throw new CryptoRuntimeException("The sodium extension is required for secrets encryption/decryption.");

            }

            $crypto = SecretsCrypto::fromBase64($this->appSecretKeyBase64);
            $manager = new SecretsManager($this->secretsFilePath, $crypto);

            $secretsArray = $manager->load();

            return new Secrets($secretsArray);
        });

        //
        // Alias class name + "secrets" string
        //
        $this->container->addShared(Secrets::class, fn () => $this->container->get(SecretsInterface::class));
        $this->container->addShared('secrets', fn () => $this->container->get(SecretsInterface::class));
    }
}

