<?php

namespace JDS\Console\Command\Secrets;

use JDS\Console\BaseCommand;
use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Security\SecretsCrypto;
use JDS\Security\SecretsManager;
use JDS\Security\SecretsValidator;

class EncryptSecretsCommand extends BaseCommand implements CommandInterface
{
    protected string $name = 'secrets:encrypt';

    protected string $description = 'Encrypt plaintext secrets into the encrypted secrets file.';

    protected array $options = [
        'validate'  => 'Validate against schema before encrypting',
        'help'      => 'Show help for this command'
    ];

    public function __construct(
        private readonly string $appSecretKey,
        private readonly string $plainPath,
        private readonly string $encPath
    )
    {
    }

    public function execute(array $params = []): int
    {
        if ($this->helpRequested($params)) {
            $this->printHelp();
            return 0;
        }

         if (!is_file($this->plainPath)) {
            $file = basename($this->plainPath, '/secrets.plaintext.json');
            $this->error("Plain secrets file missing: {$file}. [Encrypt:Secrets:Command].");
            return 1;
        }

        $json = file_get_contents($this->plainPath);
        $secrets = json_decode($json, true);

        if (!is_array($secrets)) {
            $this->error("Invalid plaintext secrets. [Encrypt:Secrets:Command].");
            return 1;
        }

        //
        // Validate before encrypting
        //
        if (isset($params['validate'])) {
            $schemaPath = dirname($this->plainPath) . '/secrets.schema.json';
            $file = basename($schemaPath, '.json');
            if (!is_file($schemaPath)) {
                $this->error("Schema file not found for validation: {$file}. [Encrypt:Secrets:Command].");
                return 1;
            }
            $schema = json_decode(file_get_contents($schemaPath), true);
            if (!is_array($schema)) {
                $this->error("Invalid secrets schema. [Encrypt:Secrets:Command].");
                return 1;
            }

            $validator = new SecretsValidator($schema);
            $validator->validate($secrets);
        }

        $crypto = SecretsCrypto::fromBase64($this->appSecretKey);
        $manager = new SecretsManager($this->encPath, $crypto);

        $manager->save($secrets);

        $this->writeln("Secrets encrypted to '{$this->encPath}'.");
        return 0;
    }
}

