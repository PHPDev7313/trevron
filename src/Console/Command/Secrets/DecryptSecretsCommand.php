<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 29, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: SecretsCommands.v1.2.FINAL.md
*/

namespace JDS\Console\Command\Secrets;

use JDS\Console\BaseCommand;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;

class DecryptSecretsCommand extends BaseCommand implements CommandInterface
{
    protected string $name = 'secrets:decrypt';

    protected string $description = 'Decrypt encrypted secrets and print them as JSON';

    protected array $options = [
        'decrypt' => "Decrypt encrypted secrets and print them as JSON",
        'help' => 'Show help for decrypt command'
    ];

    public function __construct(
        private readonly string $appSecretKey,
        private readonly string $encPath
    ) {}


    public function execute(array $params = []): int
    {
        if ($this->helpRequested($params)) {
            $this->printHelp();
            return 0;
        }

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

