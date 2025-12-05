<?php

namespace JDS\Console\Command\Secrets;

use JDS\Console\Command\BaseCommand;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;

class DecryptSecretsCommand extends BaseCommand implements CommandInterface
{
    protected string $name = 'secrets:decrypt';
    protected string $description = 'Decrypt encrypted secrets and print them as JSON';

    public function __construct(
        private readonly string $appSecretKey,
        private readonly string $encPath
    ) {}


    public function execute(array $params = []): int
    {
        if (!is_file($this->encPath)) {
            $this->error("Encrypted secrets file missing: {$this->encPath}");
            return 1;
        }

        $crypto  = SecretsCrypto::fromBase64($this->appSecretKey);
        $manager = new SecretsManager($this->encPath, $crypto);

        $secrets = $manager->load();

        $this->writeln(json_encode($secrets, JSON_PRETTY_PRINT));
        return 0;
    }
}

