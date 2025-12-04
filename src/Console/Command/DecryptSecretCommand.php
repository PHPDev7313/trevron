<?php

namespace JDS\Console\Command;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Exceptions\CryptoRuntimeException;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;

class DecryptSecretCommand implements CommandInterface
{
    public function __construct(
        private readonly string $appSecretKeyBase64,
        private readonly string $encryptedFilePath,
        private readonly ?string $outputPlainFilePath = null
    )
    {
    }

    public function execute(array $params = []): int
    {
        try {
            $crypto = SecretsCrypto::fromBase64($this->appSecretKeyBase64);
            $manager = new SecretsManager($this->encryptedFilePath, $crypto);
            $data = $manager->load();
        } catch (CryptoRuntimeException $e) {
            fwrite(STDERR, "Error decrypting secrets: {$e->getMessage()}" . PHP_EOL);
            return 1;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            fwrite(STDERR, "Failed to encode decrypted secrets as JSON." . PHP_EOL);
            return 1;
        }

        if ($this->outputPlainFilePath) {
            if (file_put_contents($this->outputPlainFilePath, $json) === false) {
                fwrite(STDERR, "Failed to write decrypted secrets as JSON." . PHP_EOL);
                return 1;
            }
            fwrite(STDOUT, "Decrypted secrets written to {$this->outputPlainFilePath}" . PHP_EOL);
        } else {
            //
            // print to stdout
            fwrite(STDOUT, $json . PHP_EOL);
        }

        return 0;
    }
}

