<?php

namespace JDS\Console\Command\Secrets;

use JDS\Contracts\Console\Command\CommandInterface;

class DecryptSecretsCommand implements CommandInterface
{
    public function __construct(
        private readonly string $appSecretKey,
        private readonly string $encPath
    ) {}


    public function execute(array $params = []): int
    {
        if (!is_file($this->encPath)) {
            fwrite(STDERR, "Encrypted secrets file missing: {$this->encPath}\n");
            return 1;
        }

        $crypto  = SecretsCrypto::fromBase64($this->appSecretKey);
        $manager = new SecretsManager($this->encPath, $crypto);

        $secrets = $manager->load();

        fwrite(STDOUT, json_encode($secrets, JSON_PRETTY_PRINT) . "\n");
        return 0;
    }
}

