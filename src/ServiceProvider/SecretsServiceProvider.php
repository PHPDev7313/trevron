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

            if (!extension_loaded('sodium')) {
                throw new CryptoRuntimeException(
                    "The sodium extension is required for secrets handling. [Secrets:Service:Provider]."
                );
            }

            $crypto = SecretsCrypto::fromBase64($config->appKeyBase64());
            $manager = new SecretsManager(
                $config->secretsFile(),
                $crypto
            );

            $secretsArray = $manager->load();

            $schema = json_decode(
                file_get_contents($config->schemaFile()),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $validator = new SecretsValidator($schema);
            $validator->validate($secretsArray);

            return new Secrets($secretsArray);
        });

        $container->addShared(
            Secrets::class,
            fn () => $container->get(SecretsInterface::class)
        );
    }
}

