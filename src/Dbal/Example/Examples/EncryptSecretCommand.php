<?php

namespace JDS\Dbal\Example\Examples;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Exceptions\CryptoRuntimeException;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;

class EncryptSecretCommand implements CommandInterface
{
    public function __construct(
        private readonly string $appSecretKeyBase64,
        private readonly string $plainFilePath,
        private readonly string $encryptFilePath
    )
    {
    }

    public function execute(array $params = []): int
    {
        if (!is_file($this->plainFilePath)) {
            fwrite(STDERR, "Plain secrets file not found: {$this->plainFilePath}" . PHP_EOL);
            return 1;
        }

        $plainJson = file_get_contents($this->plainFilePath);
        if ($plainJson === false) {
            fwrite(STDERR, "Unable to read plain secrets file: {$this->plainFilePath}". PHP_EOL);
            return 1;
        }

        $data = json_decode($plainJson, true);
        if (!is_array($data)) {
            fwrite(STDERR, "Plain secrets file is not valid JSON." . PHP_EOL);
            return 1;
        }

        try {
            $crypto = SecretsCrypto::fromBase64($this->appSecretKeyBase64);
            $manager = new SecretsManager($this->encryptFilePath, $crypto);
            $manager->save($data);
        } catch (CryptoRuntimeException $e) {
            fwrite(STDERR, "Error encrypting secrets: {$e->getMessage()}" . PHP_EOL);
            return 1;
        }

        fwrite(STDERR, "Secrets encrypted to {$this->encryptFilePath}" . PHP_EOL);
        return 0;
    }
}

