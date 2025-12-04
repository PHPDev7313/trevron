<?php

namespace JDS\Console\Command\Secrets;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsValidator;

class EncryptSecretsCommand implements CommandInterface
{
    public function __construct(
        private readonly string $appSecretKey,
        private readonly string $plainPath,
        private readonly string $encPath
    )
    {
    }

    public function execute(array $params = []): int
    {
        if (!is_file($this->plainPath)) {
            fwrite(STDERR, "Plain secrets file missing: {$this->plainPath}" . PHP_EOL);
            return 1;
        }

        $json = file_get_contents($this->plainPath);
        $secrets = json_decode($json, true);

        if (!is_array($secrets)) {
            fwrite(STDERR, "Invalid plaintext secrets." . PHP_EOL);
            return 1;
        }

        //
        // Validate before encrypting
        //
        if (isset($params['--validate'])) {
            $validator = new SecretsValidator(json_decode(file_get_contents(dirname($this->plainPath) . '/secrets.schema.json'), true));
            $validator->validate($secrets);
        }

        $crypto = SecretsCrypto::fromBase64($this->appSecretKey);
        $manager = new SecretsCrypto($this->encPath, $crypto);

        $manager->save($secrets);

        fwrite(STDOUT, "Secrets encrypted to {$this->encPath}" . PHP_EOL);
        return 0;
    }
}

